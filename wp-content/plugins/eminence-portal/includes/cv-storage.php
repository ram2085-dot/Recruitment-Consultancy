<?php
/**
 * CV file upload validation, private (non-public-URL) storage, and the one
 * capability-gated download endpoint every screen routes through (contracts/
 * candidate-data-contract.md, research.md #3). No CV file is ever linked directly —
 * every access goes through eminence_portal_maybe_serve_cv_download() below, which checks
 * current_user_can() + a nonce before it even looks up a file path, regardless of what
 * the web server would otherwise allow (Nginx doesn't read .htaccess, so the PHP check is
 * the actual guarantee, not a directory-listing block).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EMINENCE_CV_MAX_BYTES', 5 * 1024 * 1024 ); // 5MB, BRD 6.2 #16.
define( 'EMINENCE_CV_DOWNLOAD_NONCE_ACTION', 'eminence_cv_download' );

/**
 * @return string Absolute filesystem path to the private CV directory, creating it (and
 *                its access-blocking files) on first use.
 */
function eminence_cv_private_dir() {
	$upload_dir = wp_upload_dir();
	$dir        = trailingslashit( $upload_dir['basedir'] ) . 'eminence-cv-private';

	if ( ! file_exists( $dir ) ) {
		wp_mkdir_p( $dir );

		// Defense-in-depth only (research.md #3) — the real guarantee is the capability
		// check in eminence_portal_maybe_serve_cv_download(), not this file.
		file_put_contents( trailingslashit( $dir ) . '.htaccess', "Deny from all\n" );
		file_put_contents( trailingslashit( $dir ) . 'index.php', "<?php\n// Silence is golden.\n" );
	}

	return $dir;
}

/**
 * Validates an uploaded CV before it's ever moved into place (type + size, checked
 * server-side — HTML `accept`/`max` attributes are not enough on their own).
 *
 * @param array $file A single entry from $_FILES.
 * @return true|WP_Error
 */
function eminence_validate_cv_upload( array $file ) {
	if ( ! isset( $file['error'] ) || UPLOAD_ERR_NO_FILE === $file['error'] ) {
		return true; // No file is valid — CV is only mandatory unless the historical-record exception applies (FR-001), enforced by the calling form.
	}

	if ( UPLOAD_ERR_OK !== $file['error'] ) {
		return new WP_Error( 'eminence_cv_upload_error', __( 'The CV file failed to upload. Please try again.', 'eminence-portal' ) );
	}

	if ( $file['size'] > EMINENCE_CV_MAX_BYTES ) {
		return new WP_Error( 'eminence_cv_too_large', __( 'CV files must be 5MB or smaller.', 'eminence-portal' ) );
	}

	$filetype = wp_check_filetype( $file['name'], array(
		'pdf'  => 'application/pdf',
		'doc'  => 'application/msword',
		'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
	) );

	if ( ! $filetype['ext'] ) {
		return new WP_Error( 'eminence_cv_bad_type', __( 'CV files must be a PDF, DOC, or DOCX.', 'eminence-portal' ) );
	}

	return true;
}

/**
 * Moves a validated upload into the private CV directory under a randomized filename (not
 * derived from the candidate's name/email, so the path itself isn't guessable even by
 * someone who knows the candidate).
 *
 * @param array $file A single entry from $_FILES, already passed through eminence_validate_cv_upload().
 * @return string|WP_Error Relative path (stored in the candidate_name row's cv_file_path column), or WP_Error on failure.
 */
function eminence_store_cv_upload( array $file ) {
	if ( ! isset( $file['error'] ) || UPLOAD_ERR_NO_FILE === $file['error'] ) {
		return '';
	}

	$filetype  = wp_check_filetype( $file['name'] );
	$extension = $filetype['ext'] ? $filetype['ext'] : 'bin';
	$filename  = wp_generate_password( 24, false, false ) . '.' . $extension;
	$dir       = eminence_cv_private_dir();
	$dest      = trailingslashit( $dir ) . $filename;

	if ( ! @move_uploaded_file( $file['tmp_name'], $dest ) ) {
		return new WP_Error( 'eminence_cv_move_failed', __( 'The CV file could not be saved. Please try again.', 'eminence-portal' ) );
	}

	return 'eminence-cv-private/' . $filename;
}

/**
 * @param string $relative_path As stored in cv_file_path.
 * @return string|null Absolute filesystem path, or null if the value is empty/unsafe.
 */
function eminence_cv_absolute_path( $relative_path ) {
	if ( empty( $relative_path ) ) {
		return null;
	}

	// Guard against path traversal — the stored value should only ever be what
	// eminence_store_cv_upload() produced, but this is the last line of defense before
	// touching the filesystem.
	$relative_path = str_replace( array( '..', "\0" ), '', $relative_path );

	$upload_dir = wp_upload_dir();

	return trailingslashit( $upload_dir['basedir'] ) . ltrim( $relative_path, '/' );
}

/**
 * The one CV download entry point (contracts/candidate-data-contract.md). Checked on
 * `init` — early enough that nothing else has output a byte yet — via a plain query
 * parameter rather than a custom rewrite rule, matching this plugin's existing pattern
 * (auth.php checks $_POST the same direct way).
 */
add_action( 'init', 'eminence_portal_maybe_serve_cv_download' );
function eminence_portal_maybe_serve_cv_download() {
	if ( ! isset( $_GET['eminence_cv_download'] ) ) {
		return;
	}

	if ( ! current_user_can( EMINENCE_CAP_MANAGE_CANDIDATES ) ) {
		wp_die( esc_html__( 'You do not have permission to download this file.', 'eminence-portal' ), '', array( 'response' => 403 ) );
	}

	if (
		! isset( $_GET['_wpnonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), EMINENCE_CV_DOWNLOAD_NONCE_ACTION )
	) {
		wp_die( esc_html__( 'This download link has expired. Please go back and try again.', 'eminence-portal' ), '', array( 'response' => 403 ) );
	}

	$candidate_id = absint( $_GET['eminence_cv_download'] );
	$candidate    = $candidate_id ? eminence_get_candidate( $candidate_id ) : null;

	if ( ! $candidate || empty( $candidate->cv_file_path ) ) {
		wp_die( esc_html__( 'CV file not found.', 'eminence-portal' ), '', array( 'response' => 404 ) );
	}

	$path = eminence_cv_absolute_path( $candidate->cv_file_path );

	if ( ! $path || ! file_exists( $path ) ) {
		wp_die( esc_html__( 'CV file not found.', 'eminence-portal' ), '', array( 'response' => 404 ) );
	}

	$extension     = pathinfo( $path, PATHINFO_EXTENSION );
	$download_name = sanitize_file_name( $candidate->candidate_name ) . '-cv.' . $extension;

	nocache_headers();
	header( 'Content-Type: application/octet-stream' );
	header( 'Content-Disposition: attachment; filename="' . $download_name . '"' );
	header( 'Content-Length: ' . filesize( $path ) );
	readfile( $path );
	exit;
}

/**
 * @param int $candidate_id
 * @return string The gated download URL for a candidate's CV — the only URL any screen
 *                should ever render, never a direct path.
 */
function eminence_cv_download_url( $candidate_id ) {
	return wp_nonce_url(
		add_query_arg( 'eminence_cv_download', (int) $candidate_id, admin_url( 'admin.php' ) ),
		EMINENCE_CV_DOWNLOAD_NONCE_ACTION
	);
}

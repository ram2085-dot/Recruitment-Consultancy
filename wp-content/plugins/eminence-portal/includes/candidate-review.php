<?php
/**
 * "Pending Review" screen (User Story 3, 012-candidate-database) — every status =
 * pending_review record, oldest first, with the duplicate check re-run for the reviewing
 * employee (FR-002 applies here too, not just at direct-add time) and an Approve/Reject
 * decision that's always timestamped and linked to whoever made it.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EMINENCE_REVIEW_PAGE_SLUG', 'eminence-pending-review' );
define( 'EMINENCE_REVIEW_NONCE_ACTION', 'eminence_review_candidate' );

add_action( 'admin_menu', 'eminence_portal_register_review_menu' );
function eminence_portal_register_review_menu() {
	$pending_count = function_exists( 'eminence_count_candidates_by_status' )
		? eminence_count_candidates_by_status( EMINENCE_CANDIDATE_STATUS_PENDING )
		: 0;

	$label = __( 'Pending Review', 'eminence-portal' );
	if ( $pending_count > 0 ) {
		// WordPress's native admin-menu bubble (FR-013) — visible from anywhere in wp-admin.
		$label .= ' <span class="awaiting-mod count-' . (int) $pending_count . '"><span class="pending-count">' . (int) $pending_count . '</span></span>';
	}

	add_submenu_page(
		EMINENCE_DASHBOARD_PAGE_SLUG,
		__( 'Pending Review', 'eminence-portal' ),
		$label,
		EMINENCE_CAP_MANAGE_CANDIDATES,
		EMINENCE_REVIEW_PAGE_SLUG,
		'eminence_portal_render_review_page'
	);
}

function eminence_candidate_reject_reasons() {
	return array( 'Duplicate', 'Incomplete', 'Not Relevant', 'Spam' );
}

function eminence_portal_render_review_page() {
	if ( ! current_user_can( EMINENCE_CAP_MANAGE_CANDIDATES ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'eminence-portal' ) );
	}

	$result = eminence_portal_handle_review_submission();

	$reviewing = ! empty( $_GET['review'] ) ? eminence_get_candidate( absint( $_GET['review'] ) ) : null;
	if ( $reviewing && EMINENCE_CANDIDATE_STATUS_PENDING !== $reviewing->status ) {
		$reviewing = null; // Already decided (e.g. by another employee) — fall back to the list.
	}

	$pending = eminence_get_pending_candidates();
	?>
	<div class="wrap eminence-portal-screen">
		<h1><?php esc_html_e( 'Pending Review', 'eminence-portal' ); ?></h1>

		<?php if ( $result && ! empty( $result['message'] ) ) : ?>
			<div class="notice notice-<?php echo esc_attr( $result['type'] ); ?>"><p><?php echo esc_html( $result['message'] ); ?></p></div>
		<?php endif; ?>

		<?php if ( $reviewing ) : ?>
			<?php eminence_portal_render_review_form( $reviewing ); ?>
			<p><a href="<?php echo esc_url( remove_query_arg( 'review' ) ); ?>">&larr; <?php esc_html_e( 'Back to Pending Review', 'eminence-portal' ); ?></a></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Name', 'eminence-portal' ); ?></th>
						<th><?php esc_html_e( 'Department', 'eminence-portal' ); ?></th>
						<th><?php esc_html_e( 'Experience', 'eminence-portal' ); ?></th>
						<th><?php esc_html_e( 'Submitted', 'eminence-portal' ); ?></th>
						<th><?php esc_html_e( 'Action', 'eminence-portal' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! $pending ) : ?>
						<tr><td colspan="5"><?php esc_html_e( 'Nothing waiting for review.', 'eminence-portal' ); ?></td></tr>
					<?php endif; ?>
					<?php foreach ( $pending as $submission ) : ?>
						<tr>
							<td><?php echo esc_html( $submission->candidate_name ); ?></td>
							<td><?php echo esc_html( $submission->department ); ?></td>
							<td><?php echo esc_html( $submission->total_experience_years ); ?></td>
							<td><?php echo esc_html( $submission->date_added ); ?></td>
							<td><a class="button" href="<?php echo esc_url( add_query_arg( 'review', $submission->id ) ); ?>"><?php esc_html_e( 'Review', 'eminence-portal' ); ?></a></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php
}

function eminence_portal_render_review_form( $submission ) {
	// Excludes the submission's own row (it always has its own phone/email, and would
	// otherwise match itself and hide any real duplicate behind LIMIT 1 — see
	// eminence_find_duplicate_candidate()'s $exclude_id docblock).
	$duplicate = eminence_find_duplicate_candidate( $submission->phone, $submission->email, $submission->id );
	?>
	<h2><?php echo esc_html( $submission->candidate_name ); ?></h2>

	<?php if ( $duplicate ) : ?>
		<div class="notice notice-warning eminence-duplicate-compare">
			<p><strong><?php esc_html_e( 'Possible duplicate on file:', 'eminence-portal' ); ?></strong></p>
			<table class="widefat">
				<tr><th><?php esc_html_e( 'Name', 'eminence-portal' ); ?></th><td><?php echo esc_html( $duplicate->candidate_name ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Phone', 'eminence-portal' ); ?></th><td><?php echo esc_html( $duplicate->phone ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Email', 'eminence-portal' ); ?></th><td><?php echo esc_html( $duplicate->email ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Status', 'eminence-portal' ); ?></th><td><?php echo esc_html( $duplicate->status ); ?></td></tr>
			</table>
		</div>
	<?php endif; ?>

	<h3><?php esc_html_e( 'Submitted details', 'eminence-portal' ); ?></h3>
	<table class="widefat">
		<tr><th><?php esc_html_e( 'Phone', 'eminence-portal' ); ?></th><td><?php echo esc_html( $submission->phone ); ?></td></tr>
		<tr><th><?php esc_html_e( 'Email', 'eminence-portal' ); ?></th><td><?php echo esc_html( $submission->email ); ?></td></tr>
		<tr><th><?php esc_html_e( 'Experience', 'eminence-portal' ); ?></th><td><?php echo esc_html( $submission->total_experience_years ); ?></td></tr>
		<tr><th><?php esc_html_e( 'Location', 'eminence-portal' ); ?></th><td><?php echo esc_html( $submission->current_location ); ?></td></tr>
		<tr><th><?php esc_html_e( 'CTC', 'eminence-portal' ); ?></th><td><?php echo esc_html( $submission->expected_ctc ); ?></td></tr>
		<tr><th><?php esc_html_e( 'Department', 'eminence-portal' ); ?></th><td><?php echo esc_html( $submission->department ); ?></td></tr>
	</table>

	<h3><?php esc_html_e( 'Complete the remaining details before approving', 'eminence-portal' ); ?></h3>
	<form method="post" enctype="multipart/form-data">
		<?php wp_nonce_field( EMINENCE_REVIEW_NONCE_ACTION, 'eminence_review_nonce' ); ?>
		<input type="hidden" name="candidate_id" value="<?php echo esc_attr( $submission->id ); ?>" />
		<table class="form-table">
			<tr><th><label for="client_name"><?php esc_html_e( 'Client Name', 'eminence-portal' ); ?></label></th>
				<td><input type="text" name="client_name" id="client_name" class="regular-text" /></td></tr>
			<tr><th><label for="position_name"><?php esc_html_e( 'Position Name', 'eminence-portal' ); ?></label></th>
				<td><input type="text" name="position_name" id="position_name" class="regular-text" /></td></tr>
			<tr><th><label for="current_company"><?php esc_html_e( 'Current Company', 'eminence-portal' ); ?></label></th>
				<td><input type="text" name="current_company" id="current_company" class="regular-text" /></td></tr>
			<tr><th><label for="current_designation"><?php esc_html_e( 'Current Designation', 'eminence-portal' ); ?></label></th>
				<td><input type="text" name="current_designation" id="current_designation" class="regular-text" /></td></tr>
			<tr><th><label for="current_ctc"><?php esc_html_e( 'Current CTC (LPA)', 'eminence-portal' ); ?></label></th>
				<td><input type="number" step="0.1" min="0" name="current_ctc" id="current_ctc" class="small-text" /></td></tr>
			<tr><th><label for="notice_period"><?php esc_html_e( 'Notice Period', 'eminence-portal' ); ?></label></th>
				<td>
					<select name="notice_period" id="notice_period">
						<option value=""><?php esc_html_e( '— Select —', 'eminence-portal' ); ?></option>
						<?php foreach ( eminence_candidate_notice_period_options() as $option ) : ?>
							<option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option>
						<?php endforeach; ?>
					</select>
				</td></tr>
			<tr><th><label for="preferred_location"><?php esc_html_e( 'Preferred Location', 'eminence-portal' ); ?></label></th>
				<td><input type="text" name="preferred_location" id="preferred_location" class="regular-text" /></td></tr>
			<tr><th><label for="remarks"><?php esc_html_e( 'Remarks', 'eminence-portal' ); ?></label></th>
				<td><textarea name="remarks" id="remarks" class="large-text" rows="3"></textarea></td></tr>
			<tr><th><label for="cv_file"><?php esc_html_e( 'CV Attachment', 'eminence-portal' ); ?></label></th>
				<td><input type="file" name="cv_file" id="cv_file" accept=".pdf,.doc,.docx" />
					<p class="description"><?php esc_html_e( 'Optional — attach if the candidate emailed one separately.', 'eminence-portal' ); ?></p></td></tr>
		</table>

		<p>
			<button type="submit" name="eminence_review_action" value="approve" class="button button-primary"><?php esc_html_e( 'Approve', 'eminence-portal' ); ?></button>
			<select name="reject_reason">
				<option value=""><?php esc_html_e( '— Reason (optional) —', 'eminence-portal' ); ?></option>
				<?php foreach ( eminence_candidate_reject_reasons() as $reason ) : ?>
					<option value="<?php echo esc_attr( $reason ); ?>"><?php echo esc_html( $reason ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="submit" name="eminence_review_action" value="reject" class="button"><?php esc_html_e( 'Reject', 'eminence-portal' ); ?></button>
		</p>
	</form>
	<?php
}

/**
 * @return array|null Feedback to display, or null if nothing was submitted.
 */
function eminence_portal_handle_review_submission() {
	if ( empty( $_POST['eminence_review_action'] ) ) {
		return null;
	}

	if (
		! isset( $_POST['eminence_review_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eminence_review_nonce'] ) ), EMINENCE_REVIEW_NONCE_ACTION )
	) {
		return array( 'type' => 'error', 'message' => __( 'Security check failed — please try again.', 'eminence-portal' ) );
	}

	if ( ! current_user_can( EMINENCE_CAP_MANAGE_CANDIDATES ) ) {
		return array( 'type' => 'error', 'message' => __( 'You do not have permission to do that.', 'eminence-portal' ) );
	}

	$candidate_id = isset( $_POST['candidate_id'] ) ? absint( $_POST['candidate_id'] ) : 0;
	$candidate    = $candidate_id ? eminence_get_candidate( $candidate_id ) : null;

	if ( ! $candidate || EMINENCE_CANDIDATE_STATUS_PENDING !== $candidate->status ) {
		return array( 'type' => 'error', 'message' => __( 'This submission has already been decided.', 'eminence-portal' ) );
	}

	$action = sanitize_key( wp_unslash( $_POST['eminence_review_action'] ) );

	if ( 'reject' === $action ) {
		$reason = isset( $_POST['reject_reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reject_reason'] ) ) : '';
		if ( '' !== $reason && ! in_array( $reason, eminence_candidate_reject_reasons(), true ) ) {
			$reason = '';
		}

		eminence_set_candidate_status( $candidate_id, EMINENCE_CANDIDATE_STATUS_REJECTED, get_current_user_id(), $reason ?: null );

		return array( 'type' => 'success', 'message' => __( 'Submission rejected.', 'eminence-portal' ) );
	}

	if ( 'approve' === $action ) {
		$fields = array(
			'client_name'         => isset( $_POST['client_name'] ) ? sanitize_text_field( wp_unslash( $_POST['client_name'] ) ) : '',
			'position_name'       => isset( $_POST['position_name'] ) ? sanitize_text_field( wp_unslash( $_POST['position_name'] ) ) : '',
			'current_company'     => isset( $_POST['current_company'] ) ? sanitize_text_field( wp_unslash( $_POST['current_company'] ) ) : '',
			'current_designation' => isset( $_POST['current_designation'] ) ? sanitize_text_field( wp_unslash( $_POST['current_designation'] ) ) : '',
			'current_ctc'         => ( isset( $_POST['current_ctc'] ) && '' !== $_POST['current_ctc'] ) ? (float) $_POST['current_ctc'] : null,
			'notice_period'       => isset( $_POST['notice_period'] ) ? sanitize_text_field( wp_unslash( $_POST['notice_period'] ) ) : '',
			'preferred_location'  => isset( $_POST['preferred_location'] ) ? sanitize_text_field( wp_unslash( $_POST['preferred_location'] ) ) : '',
			'remarks'             => isset( $_POST['remarks'] ) ? sanitize_textarea_field( wp_unslash( $_POST['remarks'] ) ) : '',
			// BRD 6.6 step 5: Source = "Website" for an approved public submission, unless
			// the reviewer already set something else (they can't here — no source field
			// on this form, so it's always "Website"; kept as an explicit assignment, not
			// an implicit default, so it reads the same as the BRD's own wording).
			'source'              => 'Website',
		);

		if ( isset( $_FILES['cv_file'] ) && UPLOAD_ERR_NO_FILE !== $_FILES['cv_file']['error'] ) {
			$validation = eminence_validate_cv_upload( $_FILES['cv_file'] );
			if ( is_wp_error( $validation ) ) {
				return array( 'type' => 'error', 'message' => $validation->get_error_message() );
			}
			$stored = eminence_store_cv_upload( $_FILES['cv_file'] );
			if ( is_wp_error( $stored ) ) {
				return array( 'type' => 'error', 'message' => $stored->get_error_message() );
			}
			$fields['cv_file_path'] = $stored;
		}

		eminence_update_candidate( $candidate_id, $fields );
		eminence_set_candidate_status( $candidate_id, EMINENCE_CANDIDATE_STATUS_ACTIVE, get_current_user_id() );

		return array( 'type' => 'success', 'message' => __( 'Candidate approved and added to the searchable database.', 'eminence-portal' ) );
	}

	return null;
}

<?php
/**
 * [eminence_cv_submission] — the public CV submission form (User Story 3,
 * 012-candidate-database), placed on the For Candidates page (research.md #8). Captures
 * Name, Phone, Email, Experience, Location, CTC, Department — Phone/Email added to the
 * BRD's originally-listed 5 fields, since the mandatory duplicate check (FR-002,
 * constitution Principle IV) has nothing to match on without them (spec.md Assumptions).
 *
 * No login required (this is the whole point), and no candidate PII is ever shown back to
 * the visitor beyond their own submission — the duplicate check itself only ever runs
 * later, for the reviewing employee (candidate-review.php), never surfaced here
 * (Principle II: no candidate data on an unauthenticated page).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EMINENCE_PUBLIC_CV_NONCE_ACTION', 'eminence_public_cv_submission' );

add_shortcode( 'eminence_cv_submission', 'eminence_portal_render_public_cv_form' );
function eminence_portal_render_public_cv_form() {
	$result = eminence_portal_handle_public_cv_submission();

	ob_start();

	if ( $result && 'success' === $result['type'] ) {
		?>
		<div class="eminence-portal-notice eminence-portal-notice--signed_out">
			<p><?php esc_html_e( "Thanks — we've received your profile. Our recruitment team will be in touch if there's a match.", 'eminence-portal' ); ?></p>
		</div>
		<?php
		return ob_get_clean();
	}
	?>
	<div class="eminence-public-cv-form">
		<?php if ( $result && ! empty( $result['message'] ) ) : ?>
			<p class="eminence-portal-notice eminence-portal-notice--invalid"><?php echo esc_html( $result['message'] ); ?></p>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( EMINENCE_PUBLIC_CV_NONCE_ACTION, 'eminence_public_cv_nonce' ); ?>

			<p class="eminence-portal-field">
				<label for="eminence_public_name"><?php esc_html_e( 'Full Name', 'eminence-portal' ); ?></label>
				<input type="text" name="eminence_public_name" id="eminence_public_name" required />
			</p>
			<p class="eminence-portal-field">
				<label for="eminence_public_phone"><?php esc_html_e( 'Phone Number', 'eminence-portal' ); ?></label>
				<input type="text" name="eminence_public_phone" id="eminence_public_phone" required />
			</p>
			<p class="eminence-portal-field">
				<label for="eminence_public_email"><?php esc_html_e( 'Email', 'eminence-portal' ); ?></label>
				<input type="email" name="eminence_public_email" id="eminence_public_email" required />
			</p>
			<p class="eminence-portal-field">
				<label for="eminence_public_experience"><?php esc_html_e( 'Experience (Years)', 'eminence-portal' ); ?></label>
				<input type="number" step="0.1" min="0" name="eminence_public_experience" id="eminence_public_experience" required />
			</p>
			<p class="eminence-portal-field">
				<label for="eminence_public_location"><?php esc_html_e( 'Current Location', 'eminence-portal' ); ?></label>
				<input type="text" name="eminence_public_location" id="eminence_public_location" required />
			</p>
			<p class="eminence-portal-field">
				<label for="eminence_public_ctc"><?php esc_html_e( 'Current CTC (LPA)', 'eminence-portal' ); ?></label>
				<input type="number" step="0.1" min="0" name="eminence_public_ctc" id="eminence_public_ctc" required />
			</p>
			<p class="eminence-portal-field">
				<label for="eminence_public_department"><?php esc_html_e( 'Department', 'eminence-portal' ); ?></label>
				<select name="eminence_public_department" id="eminence_public_department" required>
					<option value=""><?php esc_html_e( '— Select —', 'eminence-portal' ); ?></option>
					<?php foreach ( eminence_candidate_department_options() as $option ) : ?>
						<option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>

			<p>
				<button type="submit" name="eminence_public_cv_submit" value="1" class="eminence-btn eminence-btn--primary">
					<?php esc_html_e( 'Submit Profile', 'eminence-portal' ); ?>
				</button>
			</p>
		</form>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * @return array|null Feedback to render, or null if nothing was submitted.
 */
function eminence_portal_handle_public_cv_submission() {
	if ( ! isset( $_POST['eminence_public_cv_submit'] ) ) {
		return null;
	}

	if (
		! isset( $_POST['eminence_public_cv_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eminence_public_cv_nonce'] ) ), EMINENCE_PUBLIC_CV_NONCE_ACTION )
	) {
		return array( 'type' => 'error', 'message' => __( 'Something went wrong — please try submitting again.', 'eminence-portal' ) );
	}

	$name       = isset( $_POST['eminence_public_name'] ) ? sanitize_text_field( wp_unslash( $_POST['eminence_public_name'] ) ) : '';
	$phone      = isset( $_POST['eminence_public_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['eminence_public_phone'] ) ) : '';
	$email      = isset( $_POST['eminence_public_email'] ) ? sanitize_email( wp_unslash( $_POST['eminence_public_email'] ) ) : '';
	$experience = isset( $_POST['eminence_public_experience'] ) ? (float) $_POST['eminence_public_experience'] : null;
	$location   = isset( $_POST['eminence_public_location'] ) ? sanitize_text_field( wp_unslash( $_POST['eminence_public_location'] ) ) : '';
	$ctc        = isset( $_POST['eminence_public_ctc'] ) ? (float) $_POST['eminence_public_ctc'] : null;
	$department = isset( $_POST['eminence_public_department'] ) ? sanitize_text_field( wp_unslash( $_POST['eminence_public_department'] ) ) : '';

	if ( '' === $name || '' === $phone || ! is_email( $email ) || null === $experience || '' === $location || null === $ctc || '' === $department ) {
		return array( 'type' => 'error', 'message' => __( 'Please fill in every field.', 'eminence-portal' ) );
	}

	$fields = array(
		'candidate_name'         => $name,
		'phone'                  => $phone,
		'email'                  => $email,
		'total_experience_years' => $experience,
		'current_location'       => $location,
		'expected_ctc'           => $ctc,
		'department'             => $department,
	);

	// No email/notification to the candidate beyond this on-page message (FR-016) — and
	// no duplicate-match details are ever shown here; that comparison is for the
	// reviewing employee only (candidate-review.php), never a public response.
	eminence_insert_candidate( $fields, EMINENCE_CANDIDATE_STATUS_PENDING, eminence_public_submission_user_id() );

	return array( 'type' => 'success' );
}

/**
 * added_by_user_id is NOT NULL (data-model.md), but a public submission has no logged-in
 * user — attribute it to the site's first available Portal Admin as a system placeholder
 * rather than relaxing the column's NOT NULL constraint for one edge case. The reviewing
 * employee becomes the real record of who touched it (reviewed_by_user_id) once approved.
 */
function eminence_public_submission_user_id() {
	$admins = get_users( array( 'role' => EMINENCE_ROLE_ADMIN, 'number' => 1, 'orderby' => 'ID' ) );

	return $admins ? (int) $admins[0]->ID : 0;
}

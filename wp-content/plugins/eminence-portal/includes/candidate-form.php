<?php
/**
 * "Add Candidate" screen (User Story 1, 012-candidate-database) — the internal, full
 * 20-field form (BRD 6.2). Gated on EMINENCE_CAP_MANAGE_CANDIDATES, so both Recruiter and
 * Admin can use it. Duplicate detection (FR-002) runs before every save; only an Admin
 * can waive the CV-required rule for a historical/legacy record (FR-001).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EMINENCE_ADD_CANDIDATE_PAGE_SLUG', 'eminence-add-candidate' );
define( 'EMINENCE_ADD_CANDIDATE_NONCE_ACTION', 'eminence_add_candidate' );

add_action( 'admin_menu', 'eminence_portal_register_add_candidate_menu' );
function eminence_portal_register_add_candidate_menu() {
	add_submenu_page(
		EMINENCE_DASHBOARD_PAGE_SLUG,
		__( 'Add Candidate', 'eminence-portal' ),
		__( 'Add Candidate', 'eminence-portal' ),
		EMINENCE_CAP_MANAGE_CANDIDATES,
		EMINENCE_ADD_CANDIDATE_PAGE_SLUG,
		'eminence_portal_render_add_candidate_page'
	);
}

/**
 * The department/notice-period/source option lists — one place these are defined so the
 * add form, the review form, and the search filters all offer the same choices.
 */
function eminence_candidate_department_options() {
	return array( 'Manufacturing', 'Sales', 'Procurement', 'IT', 'HR', 'Finance', 'Operations', 'Other' );
}

function eminence_candidate_notice_period_options() {
	return array( 'Immediate', '15 days', '30 days', '60 days', '90 days' );
}

function eminence_candidate_source_options() {
	return array( 'Naukri', 'Reference', 'LinkedIn', 'Website', 'Other' );
}

/**
 * Shared by the Add and Edit screens (edit mode is this same screen, reached with
 * ?candidate_id={id} — see candidate-search.php's row actions). Editing skips the
 * duplicate check (you're not creating a new record) and doesn't require a new CV upload
 * (the existing file is kept unless a new one is chosen).
 */
function eminence_portal_render_add_candidate_page() {
	if ( ! current_user_can( EMINENCE_CAP_MANAGE_CANDIDATES ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'eminence-portal' ) );
	}

	$editing = null;
	if ( ! empty( $_GET['candidate_id'] ) ) {
		$editing = eminence_get_candidate( absint( $_GET['candidate_id'] ) );
		if ( ! $editing || ! eminence_can_edit_candidate( $editing ) ) {
			wp_die( esc_html__( 'You do not have permission to edit this candidate.', 'eminence-portal' ) );
		}
	}

	$result    = eminence_portal_handle_add_candidate_submission( $editing );
	$duplicate = ( $result && ! empty( $result['duplicate'] ) ) ? $result['duplicate'] : null;

	// A successful edit updates $editing's on-screen values too, not just the DB.
	if ( $editing && $result && 'success' === $result['type'] ) {
		$editing = eminence_get_candidate( $editing->id );
	}

	$field = function ( $key ) use ( $editing ) {
		return $editing ? esc_attr( $editing->$key ) : '';
	};
	?>
	<div class="wrap eminence-portal-screen">
		<h1><?php echo $editing ? esc_html__( 'Edit Candidate', 'eminence-portal' ) : esc_html__( 'Add Candidate', 'eminence-portal' ); ?></h1>

		<?php if ( $result && ! empty( $result['message'] ) ) : ?>
			<div class="notice notice-<?php echo esc_attr( $result['type'] ); ?>">
				<p><?php echo esc_html( $result['message'] ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( $duplicate ) : ?>
			<div class="notice notice-warning eminence-duplicate-compare">
				<p><strong><?php esc_html_e( 'A matching candidate already exists:', 'eminence-portal' ); ?></strong></p>
				<table class="widefat">
					<tr><th><?php esc_html_e( 'Name', 'eminence-portal' ); ?></th><td><?php echo esc_html( $duplicate->candidate_name ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Phone', 'eminence-portal' ); ?></th><td><?php echo esc_html( $duplicate->phone ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Email', 'eminence-portal' ); ?></th><td><?php echo esc_html( $duplicate->email ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Status', 'eminence-portal' ); ?></th><td><?php echo esc_html( $duplicate->status ); ?></td></tr>
				</table>
			</div>
		<?php endif; ?>

		<form method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( EMINENCE_ADD_CANDIDATE_NONCE_ACTION, 'eminence_add_candidate_nonce' ); ?>
			<?php if ( $editing ) : ?>
				<input type="hidden" name="candidate_id" value="<?php echo esc_attr( $editing->id ); ?>" />
			<?php endif; ?>
			<table class="form-table">
				<tr><th><label for="candidate_name"><?php esc_html_e( 'Candidate Name', 'eminence-portal' ); ?> *</label></th>
					<td><input type="text" name="candidate_name" id="candidate_name" class="regular-text" value="<?php echo $field( 'candidate_name' ); ?>" required /></td></tr>
				<tr><th><label for="phone"><?php esc_html_e( 'Phone Number', 'eminence-portal' ); ?> *</label></th>
					<td><input type="text" name="phone" id="phone" class="regular-text" value="<?php echo $field( 'phone' ); ?>" required /></td></tr>
				<tr><th><label for="email"><?php esc_html_e( 'Email ID', 'eminence-portal' ); ?> *</label></th>
					<td><input type="email" name="email" id="email" class="regular-text" value="<?php echo $field( 'email' ); ?>" required /></td></tr>
				<tr><th><label for="total_experience_years"><?php esc_html_e( 'Total Experience (Years)', 'eminence-portal' ); ?> *</label></th>
					<td><input type="number" step="0.1" min="0" name="total_experience_years" id="total_experience_years" class="small-text" value="<?php echo $field( 'total_experience_years' ); ?>" required /></td></tr>
				<tr><th><label for="department"><?php esc_html_e( 'Department', 'eminence-portal' ); ?> *</label></th>
					<td>
						<select name="department" id="department" required>
							<?php foreach ( eminence_candidate_department_options() as $option ) : ?>
								<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $editing ? $editing->department : '', $option ); ?>><?php echo esc_html( $option ); ?></option>
							<?php endforeach; ?>
						</select>
					</td></tr>
				<tr><th><label for="current_location"><?php esc_html_e( 'Current Location', 'eminence-portal' ); ?></label></th>
					<td><input type="text" name="current_location" id="current_location" class="regular-text" value="<?php echo $field( 'current_location' ); ?>" /></td></tr>
				<tr><th><label for="current_company"><?php esc_html_e( 'Current Company', 'eminence-portal' ); ?></label></th>
					<td><input type="text" name="current_company" id="current_company" class="regular-text" value="<?php echo $field( 'current_company' ); ?>" /></td></tr>
				<tr><th><label for="current_designation"><?php esc_html_e( 'Current Designation', 'eminence-portal' ); ?></label></th>
					<td><input type="text" name="current_designation" id="current_designation" class="regular-text" value="<?php echo $field( 'current_designation' ); ?>" /></td></tr>
				<tr><th><label for="current_ctc"><?php esc_html_e( 'Current CTC (LPA)', 'eminence-portal' ); ?></label></th>
					<td><input type="number" step="0.1" min="0" name="current_ctc" id="current_ctc" class="small-text" value="<?php echo $field( 'current_ctc' ); ?>" /></td></tr>
				<tr><th><label for="expected_ctc"><?php esc_html_e( 'Expected CTC (LPA)', 'eminence-portal' ); ?></label></th>
					<td><input type="number" step="0.1" min="0" name="expected_ctc" id="expected_ctc" class="small-text" value="<?php echo $field( 'expected_ctc' ); ?>" /></td></tr>
				<tr><th><label for="notice_period"><?php esc_html_e( 'Notice Period', 'eminence-portal' ); ?></label></th>
					<td>
						<select name="notice_period" id="notice_period">
							<option value=""><?php esc_html_e( '— Select —', 'eminence-portal' ); ?></option>
							<?php foreach ( eminence_candidate_notice_period_options() as $option ) : ?>
								<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $editing ? $editing->notice_period : '', $option ); ?>><?php echo esc_html( $option ); ?></option>
							<?php endforeach; ?>
						</select>
					</td></tr>
				<tr><th><label for="preferred_location"><?php esc_html_e( 'Preferred Location', 'eminence-portal' ); ?></label></th>
					<td><input type="text" name="preferred_location" id="preferred_location" class="regular-text" value="<?php echo $field( 'preferred_location' ); ?>" placeholder="<?php esc_attr_e( 'Comma-separated if more than one', 'eminence-portal' ); ?>" /></td></tr>
				<tr><th><label for="client_name"><?php esc_html_e( 'Client Name', 'eminence-portal' ); ?></label></th>
					<td><input type="text" name="client_name" id="client_name" class="regular-text" value="<?php echo $field( 'client_name' ); ?>" /></td></tr>
				<tr><th><label for="position_name"><?php esc_html_e( 'Position Name', 'eminence-portal' ); ?></label></th>
					<td><input type="text" name="position_name" id="position_name" class="regular-text" value="<?php echo $field( 'position_name' ); ?>" /></td></tr>
				<tr><th><label for="profile_shared_on"><?php esc_html_e( 'Profile Shared On', 'eminence-portal' ); ?></label></th>
					<td><input type="date" name="profile_shared_on" id="profile_shared_on" value="<?php echo $field( 'profile_shared_on' ); ?>" /></td></tr>
				<tr><th><label for="source"><?php esc_html_e( 'Source', 'eminence-portal' ); ?></label></th>
					<td>
						<select name="source" id="source">
							<option value=""><?php esc_html_e( '— Select —', 'eminence-portal' ); ?></option>
							<?php foreach ( eminence_candidate_source_options() as $option ) : ?>
								<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $editing ? $editing->source : '', $option ); ?>><?php echo esc_html( $option ); ?></option>
							<?php endforeach; ?>
						</select>
					</td></tr>
				<tr><th><label for="remarks"><?php esc_html_e( 'Remarks', 'eminence-portal' ); ?></label></th>
					<td><textarea name="remarks" id="remarks" class="large-text" rows="3"><?php echo $editing ? esc_textarea( $editing->remarks ) : ''; ?></textarea></td></tr>
				<tr>
					<th><label for="cv_file"><?php esc_html_e( 'CV Attachment', 'eminence-portal' ); ?></label></th>
					<td>
						<?php if ( $editing && $editing->cv_file_path ) : ?>
							<p class="description"><?php esc_html_e( 'A CV is already on file. Choose a new file only to replace it.', 'eminence-portal' ); ?></p>
						<?php endif; ?>
						<input type="file" name="cv_file" id="cv_file" accept=".pdf,.doc,.docx" />
						<p class="description"><?php esc_html_e( 'PDF, DOC, or DOCX, max 5MB.', 'eminence-portal' ); ?></p>
						<?php if ( ! $editing && current_user_can( EMINENCE_CAP_EDIT_ANY_CANDIDATE ) ) : ?>
							<label>
								<input type="checkbox" name="is_historical_record" value="1" />
								<?php esc_html_e( 'Historical record — add without a CV (Admin exception)', 'eminence-portal' ); ?>
							</label>
						<?php endif; ?>
					</td>
				</tr>
			</table>
			<p><button type="submit" name="eminence_add_candidate_submit" value="1" class="button button-primary"><?php echo $editing ? esc_html__( 'Update Candidate', 'eminence-portal' ) : esc_html__( 'Save Candidate', 'eminence-portal' ); ?></button></p>
		</form>
	</div>
	<?php
}

/**
 * @param object|null $editing The candidate being edited, or null when adding a new one.
 * @return array|null Feedback to display, or null if nothing was submitted.
 */
function eminence_portal_handle_add_candidate_submission( $editing = null ) {
	if ( ! isset( $_POST['eminence_add_candidate_submit'] ) ) {
		return null;
	}

	if (
		! isset( $_POST['eminence_add_candidate_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eminence_add_candidate_nonce'] ) ), EMINENCE_ADD_CANDIDATE_NONCE_ACTION )
	) {
		return array( 'type' => 'error', 'message' => __( 'Security check failed — please try again.', 'eminence-portal' ) );
	}

	if ( ! current_user_can( EMINENCE_CAP_MANAGE_CANDIDATES ) ) {
		return array( 'type' => 'error', 'message' => __( 'You do not have permission to do that.', 'eminence-portal' ) );
	}

	// Re-check ownership server-side on every edit submission, not just when the page
	// first rendered — the form's hidden candidate_id could otherwise be tampered with.
	if ( $editing && ! eminence_can_edit_candidate( $editing ) ) {
		return array( 'type' => 'error', 'message' => __( 'You do not have permission to edit this candidate.', 'eminence-portal' ) );
	}

	$candidate_name = isset( $_POST['candidate_name'] ) ? sanitize_text_field( wp_unslash( $_POST['candidate_name'] ) ) : '';
	$phone          = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$email          = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$experience     = isset( $_POST['total_experience_years'] ) ? (float) $_POST['total_experience_years'] : null;
	$department     = isset( $_POST['department'] ) ? sanitize_text_field( wp_unslash( $_POST['department'] ) ) : '';
	$is_historical  = ! $editing && ! empty( $_POST['is_historical_record'] ) && current_user_can( EMINENCE_CAP_EDIT_ANY_CANDIDATE );

	$has_new_file    = isset( $_FILES['cv_file'] ) && UPLOAD_ERR_NO_FILE !== $_FILES['cv_file']['error'];
	$has_existing_cv = $editing && ! empty( $editing->cv_file_path );

	if ( '' === $candidate_name || '' === $phone || ! is_email( $email ) || null === $experience || '' === $department || ( ! $has_new_file && ! $has_existing_cv && ! $is_historical ) ) {
		return array( 'type' => 'error', 'message' => __( 'Please fill in every required field (Candidate Name, Phone, Email, Experience, Department, and CV — unless this is a historical record).', 'eminence-portal' ) );
	}

	// Mandatory duplicate check (FR-002, Principle IV) — only for a genuinely new record;
	// editing an existing one isn't "creating" a duplicate of itself.
	if ( ! $editing ) {
		$duplicate = eminence_find_duplicate_candidate( $phone, $email );
		if ( $duplicate ) {
			return array(
				'type'      => 'warning',
				'message'   => __( 'This candidate looks like a duplicate — review the existing profile below before saving again with different details.', 'eminence-portal' ),
				'duplicate' => $duplicate,
			);
		}
	}

	$cv_relative_path = $editing ? $editing->cv_file_path : '';
	if ( $has_new_file ) {
		$validation = eminence_validate_cv_upload( $_FILES['cv_file'] );
		if ( is_wp_error( $validation ) ) {
			return array( 'type' => 'error', 'message' => $validation->get_error_message() );
		}

		$stored = eminence_store_cv_upload( $_FILES['cv_file'] );
		if ( is_wp_error( $stored ) ) {
			return array( 'type' => 'error', 'message' => $stored->get_error_message() );
		}
		$cv_relative_path = $stored;
	}

	$fields = array(
		'candidate_name'          => $candidate_name,
		'phone'                   => $phone,
		'email'                   => $email,
		'total_experience_years'  => $experience,
		'department'              => $department,
		'current_location'        => isset( $_POST['current_location'] ) ? sanitize_text_field( wp_unslash( $_POST['current_location'] ) ) : '',
		'current_company'         => isset( $_POST['current_company'] ) ? sanitize_text_field( wp_unslash( $_POST['current_company'] ) ) : '',
		'current_designation'     => isset( $_POST['current_designation'] ) ? sanitize_text_field( wp_unslash( $_POST['current_designation'] ) ) : '',
		'current_ctc'             => ( '' !== $_POST['current_ctc'] ) ? (float) $_POST['current_ctc'] : null,
		'expected_ctc'            => ( '' !== $_POST['expected_ctc'] ) ? (float) $_POST['expected_ctc'] : null,
		'notice_period'           => isset( $_POST['notice_period'] ) ? sanitize_text_field( wp_unslash( $_POST['notice_period'] ) ) : '',
		'preferred_location'      => isset( $_POST['preferred_location'] ) ? sanitize_text_field( wp_unslash( $_POST['preferred_location'] ) ) : '',
		'client_name'             => isset( $_POST['client_name'] ) ? sanitize_text_field( wp_unslash( $_POST['client_name'] ) ) : '',
		'position_name'           => isset( $_POST['position_name'] ) ? sanitize_text_field( wp_unslash( $_POST['position_name'] ) ) : '',
		'profile_shared_on'       => ! empty( $_POST['profile_shared_on'] ) ? sanitize_text_field( wp_unslash( $_POST['profile_shared_on'] ) ) : null,
		'source'                  => isset( $_POST['source'] ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : '',
		'remarks'                 => isset( $_POST['remarks'] ) ? sanitize_textarea_field( wp_unslash( $_POST['remarks'] ) ) : '',
		'cv_file_path'            => $cv_relative_path,
	);

	if ( $editing ) {
		eminence_update_candidate( $editing->id, $fields );
		return array( 'type' => 'success', 'message' => __( 'Candidate updated.', 'eminence-portal' ) );
	}

	eminence_insert_candidate( $fields, EMINENCE_CANDIDATE_STATUS_ACTIVE, get_current_user_id() );

	return array( 'type' => 'success', 'message' => __( 'Candidate saved.', 'eminence-portal' ) );
}

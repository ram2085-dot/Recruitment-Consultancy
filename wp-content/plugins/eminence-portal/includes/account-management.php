<?php
/**
 * Admin-only (EMINENCE_CAP_MANAGE_EMPLOYEES) wp-admin screen: list employee accounts,
 * create new ones, and deactivate/reactivate existing ones (FR-009). WordPress itself
 * denies menu visibility and page access to anyone without the capability — see
 * research.md §6 for why this lives in wp-admin rather than a hand-built front-end screen.
 *
 * There is no "change role" action — only create and deactivate/reactivate — so demotion
 * (FR-010's other half) has no code path to guard in the first place; keeping the account
 * list to the minimum this spec actually asks for avoids scope creep (constitution
 * Principle VIII).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EMINENCE_ACCOUNTS_PAGE_SLUG', 'eminence-employee-accounts' );
define( 'EMINENCE_ACCOUNTS_NONCE_ACTION', 'eminence_manage_employees' );

// 2026-08-21 (012-candidate-database, research.md #7): moved from its own top-level menu
// into a submenu of the shared Employee Portal parent (dashboard.php) — still gated on
// EMINENCE_CAP_MANAGE_EMPLOYEES specifically, so a Recruiter (who lacks that capability,
// only EMINENCE_CAP_MANAGE_CANDIDATES) sees the parent menu and its other items but not
// this one; WordPress hides an individual submenu item the current user can't access.
add_action( 'admin_menu', 'eminence_portal_register_admin_menu' );
function eminence_portal_register_admin_menu() {
	add_submenu_page(
		EMINENCE_DASHBOARD_PAGE_SLUG,
		__( 'Employee Accounts', 'eminence-portal' ),
		__( 'Employee Accounts', 'eminence-portal' ),
		EMINENCE_CAP_MANAGE_EMPLOYEES,
		EMINENCE_ACCOUNTS_PAGE_SLUG,
		'eminence_portal_render_accounts_page'
	);
}

function eminence_portal_render_accounts_page() {
	if ( ! current_user_can( EMINENCE_CAP_MANAGE_EMPLOYEES ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'eminence-portal' ) );
	}

	$feedback = eminence_portal_handle_accounts_actions();
	$accounts = eminence_portal_get_employee_accounts();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Employee Accounts', 'eminence-portal' ); ?></h1>

		<?php if ( $feedback ) : ?>
			<div class="notice notice-<?php echo esc_attr( $feedback['type'] ); ?>">
				<p><?php echo esc_html( $feedback['message'] ); ?></p>
			</div>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Current Accounts', 'eminence-portal' ); ?></h2>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'eminence-portal' ); ?></th>
					<th><?php esc_html_e( 'Email', 'eminence-portal' ); ?></th>
					<th><?php esc_html_e( 'Role', 'eminence-portal' ); ?></th>
					<th><?php esc_html_e( 'Status', 'eminence-portal' ); ?></th>
					<th><?php esc_html_e( 'Action', 'eminence-portal' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! $accounts ) : ?>
					<tr>
						<td colspan="5"><?php esc_html_e( 'No employee accounts yet.', 'eminence-portal' ); ?></td>
					</tr>
				<?php endif; ?>
				<?php foreach ( $accounts as $account ) : ?>
					<?php $status = eminence_portal_account_status( $account->ID ); ?>
					<tr>
						<td><?php echo esc_html( $account->display_name ); ?></td>
						<td><?php echo esc_html( $account->user_email ); ?></td>
						<td><?php echo esc_html( eminence_portal_role_label( $account ) ); ?></td>
						<td><?php echo esc_html( ucfirst( $status ) ); ?></td>
						<td>
							<form method="post" style="display:inline">
								<?php wp_nonce_field( EMINENCE_ACCOUNTS_NONCE_ACTION, 'eminence_accounts_nonce' ); ?>
								<input type="hidden" name="eminence_account_id" value="<?php echo esc_attr( $account->ID ); ?>" />
								<?php if ( 'active' === $status ) : ?>
									<button type="submit" name="eminence_account_action" value="deactivate" class="button">
										<?php esc_html_e( 'Deactivate', 'eminence-portal' ); ?>
									</button>
								<?php else : ?>
									<button type="submit" name="eminence_account_action" value="activate" class="button">
										<?php esc_html_e( 'Reactivate', 'eminence-portal' ); ?>
									</button>
								<?php endif; ?>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Add Employee Account', 'eminence-portal' ); ?></h2>
		<form method="post">
			<?php wp_nonce_field( EMINENCE_ACCOUNTS_NONCE_ACTION, 'eminence_accounts_nonce' ); ?>
			<input type="hidden" name="eminence_account_action" value="create" />
			<table class="form-table">
				<tr>
					<th><label for="eminence_new_name"><?php esc_html_e( 'Name', 'eminence-portal' ); ?></label></th>
					<td><input type="text" name="eminence_new_name" id="eminence_new_name" required class="regular-text" /></td>
				</tr>
				<tr>
					<th><label for="eminence_new_email"><?php esc_html_e( 'Email', 'eminence-portal' ); ?></label></th>
					<td><input type="email" name="eminence_new_email" id="eminence_new_email" required class="regular-text" /></td>
				</tr>
				<tr>
					<th><label for="eminence_new_role"><?php esc_html_e( 'Role', 'eminence-portal' ); ?></label></th>
					<td>
						<select name="eminence_new_role" id="eminence_new_role">
							<option value="<?php echo esc_attr( EMINENCE_ROLE_RECRUITER ); ?>"><?php esc_html_e( 'Recruiter', 'eminence-portal' ); ?></option>
							<option value="<?php echo esc_attr( EMINENCE_ROLE_ADMIN ); ?>"><?php esc_html_e( 'Admin', 'eminence-portal' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="eminence_new_password"><?php esc_html_e( 'Initial Password', 'eminence-portal' ); ?></label></th>
					<td>
						<input type="text" name="eminence_new_password" id="eminence_new_password" required class="regular-text" />
						<p class="description"><?php esc_html_e( 'Share this with the employee directly; they can change it once signed in.', 'eminence-portal' ); ?></p>
					</td>
				</tr>
			</table>
			<p>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Create Account', 'eminence-portal' ); ?></button>
			</p>
		</form>
	</div>
	<?php
}

/**
 * Processes a submitted create/deactivate/activate action. Returns a feedback array
 * (type: success|error, message) to display, or null if nothing was submitted.
 */
function eminence_portal_handle_accounts_actions() {
	if ( ! isset( $_POST['eminence_account_action'] ) ) {
		return null;
	}

	if (
		! isset( $_POST['eminence_accounts_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eminence_accounts_nonce'] ) ), EMINENCE_ACCOUNTS_NONCE_ACTION )
	) {
		return array(
			'type'    => 'error',
			'message' => __( 'Security check failed — please try again.', 'eminence-portal' ),
		);
	}

	if ( ! current_user_can( EMINENCE_CAP_MANAGE_EMPLOYEES ) ) {
		return array(
			'type'    => 'error',
			'message' => __( 'You do not have permission to do that.', 'eminence-portal' ),
		);
	}

	$action = sanitize_key( wp_unslash( $_POST['eminence_account_action'] ) );

	if ( 'create' === $action ) {
		return eminence_portal_create_account();
	}

	if ( in_array( $action, array( 'activate', 'deactivate' ), true ) ) {
		return eminence_portal_set_account_status( $action );
	}

	return null;
}

function eminence_portal_create_account() {
	$name     = isset( $_POST['eminence_new_name'] ) ? sanitize_text_field( wp_unslash( $_POST['eminence_new_name'] ) ) : '';
	$email    = isset( $_POST['eminence_new_email'] ) ? sanitize_email( wp_unslash( $_POST['eminence_new_email'] ) ) : '';
	$role     = isset( $_POST['eminence_new_role'] ) ? sanitize_key( wp_unslash( $_POST['eminence_new_role'] ) ) : '';
	$password = isset( $_POST['eminence_new_password'] ) ? (string) wp_unslash( $_POST['eminence_new_password'] ) : '';

	if ( '' === $name || ! is_email( $email ) || '' === $password || ! in_array( $role, eminence_portal_roles(), true ) ) {
		return array(
			'type'    => 'error',
			'message' => __( 'Please fill in every field with a valid value.', 'eminence-portal' ),
		);
	}

	if ( email_exists( $email ) || username_exists( $email ) ) {
		return array(
			'type'    => 'error',
			'message' => __( 'An account with that email already exists.', 'eminence-portal' ),
		);
	}

	$user_id = wp_insert_user(
		array(
			'user_login'   => $email,
			'user_email'   => $email,
			'user_pass'    => $password,
			'display_name' => $name,
			'role'         => $role,
		)
	);

	if ( is_wp_error( $user_id ) ) {
		return array(
			'type'    => 'error',
			'message' => $user_id->get_error_message(),
		);
	}

	update_user_meta( $user_id, 'eminence_account_status', 'active' );

	return array(
		'type'    => 'success',
		'message' => __( 'Account created.', 'eminence-portal' ),
	);
}

function eminence_portal_set_account_status( $action ) {
	$user_id = isset( $_POST['eminence_account_id'] ) ? absint( $_POST['eminence_account_id'] ) : 0;
	$user    = $user_id ? get_userdata( $user_id ) : false;

	if ( ! $user || ! eminence_portal_is_employee( $user ) ) {
		return array(
			'type'    => 'error',
			'message' => __( 'Account not found.', 'eminence-portal' ),
		);
	}

	if ( 'deactivate' === $action ) {
		// Last-Admin guard (FR-010): never let the active Admin count drop to zero.
		if ( in_array( EMINENCE_ROLE_ADMIN, (array) $user->roles, true ) && eminence_portal_active_admin_count() <= 1 ) {
			return array(
				'type'    => 'error',
				'message' => __( 'This is the last active Admin account and cannot be deactivated.', 'eminence-portal' ),
			);
		}

		update_user_meta( $user_id, 'eminence_account_status', 'deactivated' );

		return array(
			'type'    => 'success',
			'message' => __( 'Account deactivated.', 'eminence-portal' ),
		);
	}

	update_user_meta( $user_id, 'eminence_account_status', 'active' );

	return array(
		'type'    => 'success',
		'message' => __( 'Account reactivated.', 'eminence-portal' ),
	);
}

/**
 * @return WP_User[] Every WP user holding either portal role.
 */
function eminence_portal_get_employee_accounts() {
	return get_users(
		array(
			'role__in' => eminence_portal_roles(),
			'orderby'  => 'display_name',
		)
	);
}

/**
 * @param int $user_id User ID.
 * @return string 'active' or 'deactivated' — defaults to 'active' when the meta is unset.
 */
function eminence_portal_account_status( $user_id ) {
	$status = get_user_meta( $user_id, 'eminence_account_status', true );

	return '' !== $status ? $status : 'active';
}

function eminence_portal_active_admin_count() {
	$count = 0;

	foreach ( get_users( array( 'role' => EMINENCE_ROLE_ADMIN ) ) as $admin ) {
		if ( 'active' === eminence_portal_account_status( $admin->ID ) ) {
			$count++;
		}
	}

	return $count;
}

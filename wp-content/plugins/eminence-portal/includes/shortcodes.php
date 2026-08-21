<?php
/**
 * [eminence_employee_login] — the single entry point the theme's page-employee-login.php
 * calls (contracts/portal-auth-contract.md). Renders the login form when logged out, or
 * the authenticated landing area (name, role, sign-out, and an account-management link
 * for Admins only) when logged in as an employee.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'eminence_employee_login', 'eminence_portal_render_shortcode' );
function eminence_portal_render_shortcode() {
	$user = wp_get_current_user();

	if ( is_user_logged_in() && eminence_portal_is_employee( $user ) ) {
		return eminence_portal_render_landing_area( $user );
	}

	// Logged in but not an employee account (e.g. some future non-portal WP user) is
	// treated the same as logged out — never show the employee landing area to anyone
	// this plugin doesn't recognize as staff.
	return eminence_portal_render_login_form();
}

/**
 * Authenticated landing area: confirms who is logged in and their role (FR-008), offers
 * sign-out (FR-007), and — Admins only — a link into the wp-admin account-management
 * screen (research.md §6). No candidate data of any kind appears here (FR-012).
 *
 * @param WP_User $user Current user.
 * @return string HTML.
 */
function eminence_portal_render_landing_area( $user ) {
	$role_label = eminence_portal_role_label( $user );

	ob_start();
	?>
	<div class="eminence-portal-landing">
		<p class="eminence-portal-welcome">
			<?php
			printf(
				/* translators: 1: display name, 2: role label (Admin/Recruiter) */
				esc_html__( 'Signed in as %1$s (%2$s).', 'eminence-portal' ),
				esc_html( $user->display_name ),
				esc_html( $role_label )
			);
			?>
		</p>

		<?php if ( current_user_can( EMINENCE_CAP_MANAGE_EMPLOYEES ) ) : ?>
			<p>
				<a class="eminence-btn eminence-btn--outline" href="<?php echo esc_url( admin_url( 'admin.php?page=' . EMINENCE_ACCOUNTS_PAGE_SLUG ) ); ?>">
					<?php esc_html_e( 'Manage Employee Accounts', 'eminence-portal' ); ?>
				</a>
			</p>
		<?php endif; ?>

		<form method="post" class="eminence-portal-logout-form">
			<?php wp_nonce_field( EMINENCE_LOGOUT_NONCE_ACTION, 'eminence_logout_nonce' ); ?>
			<button type="submit" name="eminence_logout_submit" value="1" class="eminence-btn eminence-btn--primary">
				<?php esc_html_e( 'Sign Out', 'eminence-portal' ); ?>
			</button>
		</form>
	</div>
	<?php
	return ob_get_clean();
}

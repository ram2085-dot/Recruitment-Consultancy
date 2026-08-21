<?php
/**
 * Login form rendering, submission handling, and sign-out.
 *
 * Credential verification itself reuses WordPress core's wp_signon() (research.md §1) —
 * this file only adds what WP core doesn't have: a single generic error message
 * regardless of cause (research.md §7), a deactivated-account check via the `authenticate`
 * filter (research.md §5), and a failed-attempt lockout (research.md §4).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EMINENCE_LOGIN_NONCE_ACTION', 'eminence_employee_login' );
define( 'EMINENCE_LOGOUT_NONCE_ACTION', 'eminence_employee_logout' );
define( 'EMINENCE_LOCKOUT_THRESHOLD', 5 );
define( 'EMINENCE_LOCKOUT_WINDOW', 15 * MINUTE_IN_SECONDS );

/**
 * Rejects sign-in for a deactivated employee account with a generic WP_Error — added
 * after WP core's own authenticate checks (priority 30) so it only runs once credentials
 * have already verified correct, and the resulting WP_Error is never shown to the visitor
 * directly (eminence_portal_handle_login_submission() below discards its message and
 * shows the same generic notice used for a wrong password).
 */
add_filter( 'authenticate', 'eminence_portal_block_deactivated_login', 30 );
function eminence_portal_block_deactivated_login( $user ) {
	if ( ! ( $user instanceof WP_User ) || ! eminence_portal_is_employee( $user ) ) {
		return $user;
	}

	$status = get_user_meta( $user->ID, 'eminence_account_status', true );

	// Missing meta defaults to active (e.g. accounts created directly via WP-CLI in testing).
	if ( '' !== $status && 'active' !== $status ) {
		return new WP_Error( 'eminence_account_inactive', __( 'This account is not available.', 'eminence-portal' ) );
	}

	return $user;
}

add_action( 'template_redirect', 'eminence_portal_handle_auth_submission', 5 );
function eminence_portal_handle_auth_submission() {
	if ( isset( $_POST['eminence_login_submit'] ) ) {
		eminence_portal_handle_login_submission();
	} elseif ( isset( $_POST['eminence_logout_submit'] ) ) {
		eminence_portal_handle_logout_submission();
	}
}

function eminence_portal_handle_login_submission() {
	if (
		! isset( $_POST['eminence_login_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eminence_login_nonce'] ) ), EMINENCE_LOGIN_NONCE_ACTION )
	) {
		eminence_portal_redirect_to_login( 'invalid' );
	}

	$identifier = isset( $_POST['eminence_login_identifier'] ) ? sanitize_text_field( wp_unslash( $_POST['eminence_login_identifier'] ) ) : '';
	$password   = isset( $_POST['eminence_login_password'] ) ? (string) wp_unslash( $_POST['eminence_login_password'] ) : '';

	if ( '' === $identifier || eminence_portal_is_locked_out( $identifier ) ) {
		eminence_portal_log_signin( $identifier, 'blocked' );
		eminence_portal_redirect_to_login( 'invalid' );
	}

	$user = wp_signon(
		array(
			'user_login'    => $identifier,
			'user_password' => $password,
			'remember'      => false,
		),
		is_ssl()
	);

	if ( is_wp_error( $user ) || ! eminence_portal_is_employee( $user ) ) {
		eminence_portal_register_failed_attempt( $identifier );
		eminence_portal_log_signin( $identifier, 'failed' );
		eminence_portal_redirect_to_login( 'invalid' );
	}

	eminence_portal_clear_failed_attempts( $identifier );
	update_user_meta( $user->ID, 'eminence_last_activity', time() );
	eminence_portal_log_signin( $identifier, 'success' );

	wp_safe_redirect( eminence_portal_login_page_url() );
	exit;
}

function eminence_portal_handle_logout_submission() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	if (
		! isset( $_POST['eminence_logout_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eminence_logout_nonce'] ) ), EMINENCE_LOGOUT_NONCE_ACTION )
	) {
		return;
	}

	wp_logout();
	eminence_portal_redirect_to_login( 'signed_out' );
}

/**
 * Failed-attempt lockout (research.md §4) — 5 failures within 15 minutes locks the
 * account for 15 minutes. Tracked via a transient keyed to the identifier, no new
 * database table.
 */
function eminence_portal_lockout_key( $identifier ) {
	return 'eminence_lockout_' . md5( strtolower( $identifier ) );
}

function eminence_portal_is_locked_out( $identifier ) {
	return (int) get_transient( eminence_portal_lockout_key( $identifier ) ) >= EMINENCE_LOCKOUT_THRESHOLD;
}

function eminence_portal_register_failed_attempt( $identifier ) {
	$key   = eminence_portal_lockout_key( $identifier );
	$count = (int) get_transient( $key );
	set_transient( $key, $count + 1, EMINENCE_LOCKOUT_WINDOW );
}

function eminence_portal_clear_failed_attempts( $identifier ) {
	delete_transient( eminence_portal_lockout_key( $identifier ) );
}

/**
 * Sign-in/sign-out event log (FR-013) — outcome and identifier only, never a password.
 */
function eminence_portal_log_signin( $identifier, $outcome ) {
	error_log( sprintf( '[eminence-portal] sign-in %s for "%s"', $outcome, $identifier ) );
}

/**
 * The Employee Login page URL — used both to redirect back to after a form submission
 * and as the target a logged-out visitor is sent to. Looked up by slug, matching the
 * pattern the theme itself already uses (see front-page.php's get_page_by_path() calls).
 */
function eminence_portal_login_page_url() {
	$page = get_page_by_path( 'employee-login' );

	return $page ? get_permalink( $page ) : home_url( '/' );
}

function eminence_portal_redirect_to_login( $notice ) {
	wp_safe_redirect( add_query_arg( 'eminence_notice', $notice, eminence_portal_login_page_url() ) );
	exit;
}

/**
 * One generic message per notice code — never a distinct message for "wrong password"
 * vs. "account deactivated" vs. "locked out" (research.md §7). "timeout"/"deactivated"
 * (mid-session) and "signed_out" are safe to be specific since, by definition, whoever
 * sees them was already authenticated as that account.
 */
function eminence_portal_notice_message( $notice ) {
	switch ( $notice ) {
		case 'invalid':
			return __( "The email/password you entered doesn't match our records.", 'eminence-portal' );
		case 'timeout':
			return __( 'Your session expired after a period of inactivity — please sign in again.', 'eminence-portal' );
		case 'deactivated':
			return __( 'This account is no longer available.', 'eminence-portal' );
		case 'signed_out':
			return __( 'You have been signed out.', 'eminence-portal' );
		default:
			return '';
	}
}

/**
 * Renders the login form (logged-out branch of the [eminence_employee_login] shortcode).
 *
 * @return string HTML.
 */
function eminence_portal_render_login_form() {
	$notice = isset( $_GET['eminence_notice'] ) ? sanitize_key( wp_unslash( $_GET['eminence_notice'] ) ) : '';

	ob_start();
	?>
	<div class="eminence-portal-login">
		<?php if ( $notice && eminence_portal_notice_message( $notice ) ) : ?>
			<p class="eminence-portal-notice eminence-portal-notice--<?php echo esc_attr( $notice ); ?>">
				<?php echo esc_html( eminence_portal_notice_message( $notice ) ); ?>
			</p>
		<?php endif; ?>

		<form method="post" class="eminence-portal-login-form">
			<?php wp_nonce_field( EMINENCE_LOGIN_NONCE_ACTION, 'eminence_login_nonce' ); ?>

			<p class="eminence-portal-field">
				<label for="eminence_login_identifier"><?php esc_html_e( 'Email', 'eminence-portal' ); ?></label>
				<input type="email" name="eminence_login_identifier" id="eminence_login_identifier" required autocomplete="username" />
			</p>

			<p class="eminence-portal-field">
				<label for="eminence_login_password"><?php esc_html_e( 'Password', 'eminence-portal' ); ?></label>
				<input type="password" name="eminence_login_password" id="eminence_login_password" required autocomplete="current-password" />
			</p>

			<p>
				<button type="submit" name="eminence_login_submit" value="1" class="eminence-btn eminence-btn--primary">
					<?php esc_html_e( 'Sign In', 'eminence-portal' ); ?>
				</button>
			</p>
		</form>
	</div>
	<?php
	return ob_get_clean();
}

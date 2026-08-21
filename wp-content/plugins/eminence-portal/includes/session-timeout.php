<?php
/**
 * Enforces the 30-minute inactivity session timeout (FR-006, research.md §3) and, using
 * the same request-time check, signs out an account that was deactivated mid-session
 * (spec.md Edge Cases, research.md §5). WordPress core has no idle-timeout of its own —
 * its auth cookies are valid for a fixed duration regardless of activity — so this is
 * entirely custom.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EMINENCE_SESSION_IDLE_LIMIT', 30 * MINUTE_IN_SECONDS );

/**
 * Runs on every request, after the login/logout submission handler (auth.php, priority 5)
 * has already had a chance to `exit` on its own. Order matters: the idle check below
 * reads the *previous* last-activity value before this function refreshes it — refreshing
 * first would mean every request always looks "just active" and the timeout could never
 * trigger.
 */
add_action( 'template_redirect', 'eminence_portal_enforce_session', 10 );
function eminence_portal_enforce_session() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	$user = wp_get_current_user();

	if ( ! eminence_portal_is_employee( $user ) ) {
		return; // Not a portal account — no session policy from this plugin applies.
	}

	$status = get_user_meta( $user->ID, 'eminence_account_status', true );
	if ( '' !== $status && 'active' !== $status ) {
		wp_logout();
		eminence_portal_redirect_to_login( 'deactivated' );
	}

	// Checked against '', not PHP truthiness — a real last-activity value of exactly 0
	// (epoch) must still be treated as a timestamp to compare, not as "never recorded".
	$last_activity_raw = get_user_meta( $user->ID, 'eminence_last_activity', true );

	if ( '' !== $last_activity_raw && ( time() - (int) $last_activity_raw ) > EMINENCE_SESSION_IDLE_LIMIT ) {
		wp_logout();
		eminence_portal_redirect_to_login( 'timeout' );
	}

	update_user_meta( $user->ID, 'eminence_last_activity', time() );
}

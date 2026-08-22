<?php
/**
 * Registers the two custom roles this plugin needs, on activation only.
 *
 * Deliberately NOT the built-in WordPress `administrator` role for the BRD's "Admin"
 * business role — see research.md §2. A Portal Admin employee gets exactly `read` (the
 * baseline WordPress needs to allow any wp-admin access at all) plus
 * EMINENCE_CAP_MANAGE_EMPLOYEES, nothing else — they cannot install plugins, edit the
 * theme, or manage any WordPress user outside this plugin's own screen.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function eminence_portal_activate() {
	add_role(
		EMINENCE_ROLE_RECRUITER,
		__( 'Recruiter', 'eminence-portal' ),
		array(
			'read' => true,
		)
	);

	add_role(
		EMINENCE_ROLE_ADMIN,
		__( 'Portal Admin', 'eminence-portal' ),
		array(
			'read'                        => true,
			EMINENCE_CAP_MANAGE_EMPLOYEES => true,
		)
	);

	// add_role() above only creates a role if the slug doesn't already exist — on a fresh
	// install both roles are new, so this call is what actually attaches the candidate
	// capabilities the first time. On an install upgrading from 011 (roles already exist),
	// eminence_portal_maybe_upgrade() (candidates-schema.php) calls this same function.
	eminence_portal_grant_candidate_capabilities();

	if ( function_exists( 'eminence_portal_create_candidates_table' ) ) {
		eminence_portal_create_candidates_table();
	}

	if ( function_exists( 'eminence_portal_schedule_retention_sweep' ) ) {
		eminence_portal_schedule_retention_sweep();
	}
}

/**
 * Retrofits the 012-candidate-database capabilities onto both existing roles
 * (research.md #6) — add_role() does not update an already-existing role's capability
 * set, so this has to be its own explicit step, not just part of the add_role() calls
 * above, to actually reach an install where the roles predate this feature.
 */
function eminence_portal_grant_candidate_capabilities() {
	$recruiter = get_role( EMINENCE_ROLE_RECRUITER );
	if ( $recruiter ) {
		$recruiter->add_cap( EMINENCE_CAP_MANAGE_CANDIDATES );
	}

	$admin = get_role( EMINENCE_ROLE_ADMIN );
	if ( $admin ) {
		$admin->add_cap( EMINENCE_CAP_MANAGE_CANDIDATES );
		$admin->add_cap( EMINENCE_CAP_EDIT_ANY_CANDIDATE );
	}
}

/**
 * Deactivating the plugin must never lock anyone out or delete data — it leaves the
 * roles and every employee account exactly as they are. Only an explicit uninstall
 * (out of scope for this feature) would remove the roles themselves.
 */
function eminence_portal_deactivate() {
	// Leaves roles, employee accounts, and every candidate record untouched — see docblock
	// above. Only the scheduled retention-sweep cron event is cleared, since an orphaned
	// scheduled event (not user data) is just housekeeping, not a data-loss risk.
	wp_clear_scheduled_hook( 'eminence_portal_daily_retention_sweep' );
}

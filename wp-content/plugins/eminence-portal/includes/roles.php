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
}

/**
 * Deactivating the plugin must never lock anyone out or delete data — it leaves the
 * roles and every employee account exactly as they are. Only an explicit uninstall
 * (out of scope for this feature) would remove the roles themselves.
 */
function eminence_portal_deactivate() {
	// Intentionally empty — see docblock above.
}

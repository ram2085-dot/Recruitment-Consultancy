<?php
/**
 * Role/capability constants and small helpers reused everywhere access is checked.
 * Central source of truth so a role slug or capability name is never typed as a raw
 * string in more than one place (specs/011-employee-login/data-model.md).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EMINENCE_ROLE_RECRUITER', 'eminence_recruiter' );
define( 'EMINENCE_ROLE_ADMIN', 'eminence_portal_admin' );
define( 'EMINENCE_CAP_MANAGE_EMPLOYEES', 'eminence_manage_employees' );

/**
 * The two roles this plugin manages, in a fixed order — not an open-ended/growing list
 * (data-model.md "Role").
 *
 * @return string[] Role slugs.
 */
function eminence_portal_roles() {
	return array( EMINENCE_ROLE_RECRUITER, EMINENCE_ROLE_ADMIN );
}

/**
 * Human-readable role label for a WP_User, matching BRD Section 6.1's business-facing
 * names ("Recruiter" / "Admin") rather than the internal role slugs.
 *
 * @param WP_User $user User to label.
 * @return string Role label, or '' if the user holds neither portal role.
 */
function eminence_portal_role_label( $user ) {
	if ( in_array( EMINENCE_ROLE_ADMIN, (array) $user->roles, true ) ) {
		return __( 'Admin', 'eminence-portal' );
	}

	if ( in_array( EMINENCE_ROLE_RECRUITER, (array) $user->roles, true ) ) {
		return __( 'Recruiter', 'eminence-portal' );
	}

	return '';
}

/**
 * Whether a WP_User holds either portal role at all — used to distinguish "an employee
 * account" from any other WordPress user (e.g. a future customer-facing account type this
 * plugin knows nothing about).
 *
 * @param WP_User $user User to check.
 * @return bool
 */
function eminence_portal_is_employee( $user ) {
	return (bool) array_intersect( eminence_portal_roles(), (array) $user->roles );
}

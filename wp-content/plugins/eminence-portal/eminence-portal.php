<?php
/**
 * Plugin Name: Eminence Portal
 * Description: Employee authentication and internal Employee Portal features for
 *               Eminence Consultant (Module 2 / specs/011-employee-login). Kept as a
 *               separate plugin from the eminence-consultant theme per constitution
 *               Principle I (Fixed Technology Stack) — a future theme redesign can never
 *               accidentally touch this security-critical code, and this plugin has no
 *               dependency on the theme.
 * Version:     1.0.0
 * Author:      Eminence Consultant
 * License:     Proprietary
 *
 * Architecture (see specs/011-employee-login/plan.md and research.md for the "why"):
 * employee accounts are WordPress users carrying one of two custom roles
 * (eminence_recruiter, eminence_portal_admin) registered in includes/roles.php.
 * Authentication reuses WordPress core's own wp_signon()/password hashing rather than a
 * hand-rolled credential store. The public entry point is the [eminence_employee_login]
 * shortcode, called from the theme's page-employee-login.php — that is the entire
 * plugin<->theme integration surface (contracts/portal-auth-contract.md).
 */

// Disallow direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EMINENCE_PORTAL_DIR', plugin_dir_path( __FILE__ ) );
define( 'EMINENCE_PORTAL_URL', plugin_dir_url( __FILE__ ) );
define( 'EMINENCE_PORTAL_VERSION', '1.0.0' );

require_once EMINENCE_PORTAL_DIR . 'includes/capabilities.php';
require_once EMINENCE_PORTAL_DIR . 'includes/roles.php';
require_once EMINENCE_PORTAL_DIR . 'includes/auth.php';
require_once EMINENCE_PORTAL_DIR . 'includes/session-timeout.php';
require_once EMINENCE_PORTAL_DIR . 'includes/account-management.php';
require_once EMINENCE_PORTAL_DIR . 'includes/shortcodes.php';

register_activation_hook( __FILE__, 'eminence_portal_activate' );
register_deactivation_hook( __FILE__, 'eminence_portal_deactivate' );

/**
 * Enqueue the plugin's own stylesheet only on the page that actually needs it (the
 * Employee Login page and, by extension, the wp-admin account-management screen gets its
 * own separate enqueue in account-management.php) — no reason to load this on every page.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! is_page_template( 'page-employee-login.php' ) ) {
			return;
		}

		wp_enqueue_style(
			'eminence-portal',
			EMINENCE_PORTAL_URL . 'assets/css/portal.css',
			array(),
			EMINENCE_PORTAL_VERSION
		);
	}
);

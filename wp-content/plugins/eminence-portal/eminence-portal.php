<?php
/**
 * Plugin Name: Eminence Portal
 * Description: Employee authentication and internal Employee Portal features for
 *               Eminence Consultant (Module 2 / specs/011-employee-login). Kept as a
 *               separate plugin from the eminence-consultant theme per constitution
 *               Principle I (Fixed Technology Stack) — a future theme redesign can never
 *               accidentally touch this security-critical code, and this plugin has no
 *               dependency on the theme.
 * Version:     1.1.0
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
 *
 * 012-candidate-database (v1.1.0) extends this with a candidate database (one custom
 * table, not postmeta — see that spec's research.md #1), CV upload/download,
 * search/filter, a public submission form, and the Approve/Reject review workflow — see
 * contracts/candidate-data-contract.md for what it does and doesn't expose to later
 * features. eminence_portal_maybe_upgrade() (candidates-schema.php) is what retrofits
 * this onto the install that was already active from 011, since a plain
 * register_activation_hook() addition wouldn't otherwise run again.
 */

// Disallow direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EMINENCE_PORTAL_DIR', plugin_dir_path( __FILE__ ) );
define( 'EMINENCE_PORTAL_URL', plugin_dir_url( __FILE__ ) );
define( 'EMINENCE_PORTAL_VERSION', '1.1.0' );

require_once EMINENCE_PORTAL_DIR . 'includes/capabilities.php';
require_once EMINENCE_PORTAL_DIR . 'includes/roles.php';
require_once EMINENCE_PORTAL_DIR . 'includes/candidates-schema.php';
require_once EMINENCE_PORTAL_DIR . 'includes/candidate-repository.php';
require_once EMINENCE_PORTAL_DIR . 'includes/cv-storage.php';
require_once EMINENCE_PORTAL_DIR . 'includes/auth.php';
require_once EMINENCE_PORTAL_DIR . 'includes/session-timeout.php';
require_once EMINENCE_PORTAL_DIR . 'includes/account-management.php';
require_once EMINENCE_PORTAL_DIR . 'includes/shortcodes.php';
require_once EMINENCE_PORTAL_DIR . 'includes/candidate-form.php';
require_once EMINENCE_PORTAL_DIR . 'includes/candidate-search.php';
require_once EMINENCE_PORTAL_DIR . 'includes/candidate-review.php';
require_once EMINENCE_PORTAL_DIR . 'includes/public-cv-form.php';
require_once EMINENCE_PORTAL_DIR . 'includes/dashboard.php';

register_activation_hook( __FILE__, 'eminence_portal_activate' );
register_deactivation_hook( __FILE__, 'eminence_portal_deactivate' );

/**
 * Enqueue the plugin's own stylesheet only on pages that actually need it — the Employee
 * Login page, and (012-candidate-database) any page whose content includes the public CV
 * submission shortcode, wherever the business owner has placed it. The wp-admin screens
 * get their own separate enqueue in account-management.php.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		$needs_portal_css = is_page_template( 'page-employee-login.php' )
			|| ( is_singular() && has_shortcode( get_post()->post_content, 'eminence_cv_submission' ) );

		if ( ! $needs_portal_css ) {
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

/**
 * Same stylesheet, on this plugin's own wp-admin screens only (Dashboard, Employee
 * Accounts, Candidates, Add/Edit Candidate, Pending Review) — checked by page slug via
 * $_GET['page'] rather than the $hook_suffix admin_enqueue_scripts passes, since every one
 * of these screens is a submenu of the same parent and WordPress's hook suffixes for
 * submenus aren't as predictable/stable to match against as the page slug already is.
 */
add_action(
	'admin_enqueue_scripts',
	function () {
		$portal_pages = array(
			EMINENCE_DASHBOARD_PAGE_SLUG,
			EMINENCE_ACCOUNTS_PAGE_SLUG,
			EMINENCE_CANDIDATES_PAGE_SLUG,
			EMINENCE_ADD_CANDIDATE_PAGE_SLUG,
			EMINENCE_REVIEW_PAGE_SLUG,
		);

		if ( empty( $_GET['page'] ) || ! in_array( $_GET['page'], $portal_pages, true ) ) {
			return;
		}

		wp_enqueue_style(
			'eminence-portal-admin',
			EMINENCE_PORTAL_URL . 'assets/css/portal.css',
			array(),
			EMINENCE_PORTAL_VERSION
		);
	}
);

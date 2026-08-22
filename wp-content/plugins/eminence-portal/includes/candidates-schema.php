<?php
/**
 * Candidate database table (012-candidate-database) — one table, not a custom post type
 * with postmeta, specifically for indexed multi-field range-query performance at up to
 * 10,000 rows (SC-003). See data-model.md for the full column/index rationale.
 *
 * Also owns the plugin's upgrade-check mechanism: eminence-portal was already active
 * (011-employee-login) before this feature shipped, so a plain register_activation_hook()
 * addition wouldn't run for that already-active install — add_role() also does not update
 * an already-existing role's capabilities. eminence_portal_maybe_upgrade(), hooked to
 * plugins_loaded and compared against a stored option, is what makes both the new table
 * and the new capabilities (roles.php) actually land on an install that never re-activates.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return string The candidates table name, with the site's own $wpdb prefix.
 */
function eminence_candidates_table() {
	global $wpdb;
	return $wpdb->prefix . 'eminence_candidates';
}

function eminence_portal_create_candidates_table() {
	global $wpdb;

	$table_name      = eminence_candidates_table();
	$charset_collate = $wpdb->get_charset_collate();

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	// dbDelta() is picky about exact formatting (two spaces before KEY, etc.) — see
	// https://developer.wordpress.org/reference/functions/dbdelta/ — kept to its expected
	// style throughout, not this project's usual tab-indent PHP style, so dbDelta parses it.
	$sql = "CREATE TABLE $table_name (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		client_name VARCHAR(190) NULL,
		position_name VARCHAR(190) NULL,
		profile_shared_on DATE NULL,
		candidate_name VARCHAR(190) NOT NULL,
		phone VARCHAR(20) NOT NULL,
		email VARCHAR(190) NOT NULL,
		current_location VARCHAR(190) NULL,
		total_experience_years DECIMAL(4,1) NOT NULL,
		current_company VARCHAR(190) NULL,
		current_designation VARCHAR(190) NULL,
		department VARCHAR(100) NOT NULL,
		current_ctc DECIMAL(8,2) NULL,
		expected_ctc DECIMAL(8,2) NULL,
		notice_period VARCHAR(30) NULL,
		preferred_location VARCHAR(190) NULL,
		cv_file_path VARCHAR(255) NULL,
		added_by_user_id BIGINT UNSIGNED NOT NULL,
		date_added DATETIME NOT NULL,
		source VARCHAR(50) NULL,
		remarks TEXT NULL,
		status VARCHAR(20) NOT NULL,
		reviewed_by_user_id BIGINT UNSIGNED NULL,
		reviewed_at DATETIME NULL,
		reject_reason VARCHAR(30) NULL,
		last_activity_at DATETIME NOT NULL,
		created_at DATETIME NOT NULL,
		updated_at DATETIME NOT NULL,
		PRIMARY KEY  (id),
		KEY phone (phone),
		KEY email (email),
		KEY department (department),
		KEY current_location (current_location),
		KEY total_experience_years (total_experience_years),
		KEY current_ctc (current_ctc),
		KEY expected_ctc (expected_ctc),
		KEY notice_period (notice_period),
		KEY added_by_user_id (added_by_user_id),
		KEY status (status)
	) $charset_collate;";

	dbDelta( $sql );
}

/**
 * Runs the retrofit needed on every already-active install: new table (if missing) +
 * new capabilities on the existing roles (roles.php). Idempotent — safe to call on every
 * plugins_loaded once the version check below gates it to only actually happen once.
 */
function eminence_portal_maybe_upgrade() {
	$installed_version = get_option( 'eminence_portal_version', '1.0.0' );

	if ( version_compare( $installed_version, EMINENCE_PORTAL_VERSION, '>=' ) ) {
		return;
	}

	eminence_portal_create_candidates_table();
	eminence_portal_grant_candidate_capabilities();
	eminence_portal_schedule_retention_sweep();

	update_option( 'eminence_portal_version', EMINENCE_PORTAL_VERSION );
}
add_action( 'plugins_loaded', 'eminence_portal_maybe_upgrade' );

<?php
/**
 * Dashboard (User Story 4, 012-candidate-database) — the top-level "Employee Portal" menu
 * parent (research.md #7); Employee Accounts, Candidates, Add Candidate, and Pending
 * Review all register as its submenus (see each of those files). Four cards: employee
 * count, active-CV count, pending-review count, and recent logins — all read-only
 * aggregation over data User Stories 1-3 (and 011-employee-login) already produce, no new
 * entity of its own (data-model.md).
 *
 * Visible to both roles (spec.md User Story 4 doesn't restrict it to Admin) — an Admin
 * additionally lands here immediately after signing in (auth.php).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'eminence_portal_register_dashboard_menu', 5 ); // Priority 5: registers the parent before the submenus above (cosmetic ordering only — see candidate-form.php etc. for why load order doesn't otherwise matter).
function eminence_portal_register_dashboard_menu() {
	add_menu_page(
		__( 'Employee Portal', 'eminence-portal' ),
		__( 'Employee Portal', 'eminence-portal' ),
		EMINENCE_CAP_MANAGE_CANDIDATES,
		EMINENCE_DASHBOARD_PAGE_SLUG,
		'eminence_portal_render_dashboard_page',
		'dashicons-businessperson',
		57
	);

	add_submenu_page(
		EMINENCE_DASHBOARD_PAGE_SLUG,
		__( 'Dashboard', 'eminence-portal' ),
		__( 'Dashboard', 'eminence-portal' ),
		EMINENCE_CAP_MANAGE_CANDIDATES,
		EMINENCE_DASHBOARD_PAGE_SLUG,
		'eminence_portal_render_dashboard_page'
	);
}

/**
 * @return array WP_User[] of employees, most-recently-signed-in first, per
 * eminence_last_activity user meta (011-employee-login) — the closest thing to a sign-in
 * timestamp already tracked; a dedicated sign-in log was left to that feature's own
 * FR-013 as an error_log() line, not a queryable table, so this reuses the timestamp that
 * IS queryable rather than parsing logs.
 */
function eminence_portal_get_recent_logins( $limit = 5 ) {
	$employees = eminence_portal_get_employee_accounts();

	usort(
		$employees,
		function ( $a, $b ) {
			$a_time = (int) get_user_meta( $a->ID, 'eminence_last_activity', true );
			$b_time = (int) get_user_meta( $b->ID, 'eminence_last_activity', true );
			return $b_time <=> $a_time;
		}
	);

	return array_slice( $employees, 0, $limit );
}

function eminence_portal_render_dashboard_page() {
	if ( ! current_user_can( EMINENCE_CAP_MANAGE_CANDIDATES ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'eminence-portal' ) );
	}

	$employee_count = count( eminence_portal_get_employee_accounts() );
	$active_count   = eminence_count_candidates_by_status( EMINENCE_CANDIDATE_STATUS_ACTIVE );
	$pending_count  = eminence_count_candidates_by_status( EMINENCE_CANDIDATE_STATUS_PENDING );
	$recent_logins  = eminence_portal_get_recent_logins();
	?>
	<div class="wrap eminence-portal-screen">
		<h1><?php esc_html_e( 'Dashboard', 'eminence-portal' ); ?></h1>

		<div class="eminence-dashboard-cards">
			<div class="eminence-dashboard-card">
				<span class="eminence-dashboard-card-number"><?php echo esc_html( $employee_count ); ?></span>
				<span class="eminence-dashboard-card-label"><?php esc_html_e( 'Employee Accounts', 'eminence-portal' ); ?></span>
			</div>
			<div class="eminence-dashboard-card">
				<span class="eminence-dashboard-card-number"><?php echo esc_html( $active_count ); ?></span>
				<span class="eminence-dashboard-card-label"><?php esc_html_e( 'Active CVs', 'eminence-portal' ); ?></span>
			</div>
			<div class="eminence-dashboard-card">
				<span class="eminence-dashboard-card-number"><?php echo esc_html( $pending_count ); ?></span>
				<span class="eminence-dashboard-card-label"><?php esc_html_e( 'Pending Review', 'eminence-portal' ); ?></span>
				<?php if ( $pending_count > 0 ) : ?>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . EMINENCE_REVIEW_PAGE_SLUG ) ); ?>"><?php esc_html_e( 'Review now', 'eminence-portal' ); ?></a>
				<?php endif; ?>
			</div>
		</div>

		<h2><?php esc_html_e( 'Recent Logins', 'eminence-portal' ); ?></h2>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'eminence-portal' ); ?></th>
					<th><?php esc_html_e( 'Role', 'eminence-portal' ); ?></th>
					<th><?php esc_html_e( 'Last Activity', 'eminence-portal' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! $recent_logins ) : ?>
					<tr><td colspan="3"><?php esc_html_e( 'No sign-ins recorded yet.', 'eminence-portal' ); ?></td></tr>
				<?php endif; ?>
				<?php foreach ( $recent_logins as $employee ) : ?>
					<?php $last_activity = (int) get_user_meta( $employee->ID, 'eminence_last_activity', true ); ?>
					<tr>
						<td><?php echo esc_html( $employee->display_name ); ?></td>
						<td><?php echo esc_html( eminence_portal_role_label( $employee ) ); ?></td>
						<td><?php echo $last_activity ? esc_html( human_time_diff( $last_activity ) . ' ago' ) : esc_html__( 'Never', 'eminence-portal' ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

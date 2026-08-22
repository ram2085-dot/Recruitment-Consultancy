<?php
/**
 * "Candidates" search/filter screen (User Story 2, 012-candidate-database). Every query
 * here goes through eminence_search_candidates()/eminence_count_search_results()
 * (candidate-repository.php), which always scope to status = active — a pending or
 * rejected record structurally cannot appear here (FR-009).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EMINENCE_CANDIDATES_PAGE_SLUG', 'eminence-candidates' );
define( 'EMINENCE_CANDIDATE_DELETE_NONCE_ACTION', 'eminence_delete_candidate' );

add_action( 'admin_menu', 'eminence_portal_register_candidates_menu' );
function eminence_portal_register_candidates_menu() {
	add_submenu_page(
		EMINENCE_DASHBOARD_PAGE_SLUG,
		__( 'Candidates', 'eminence-portal' ),
		__( 'Candidates', 'eminence-portal' ),
		EMINENCE_CAP_MANAGE_CANDIDATES,
		EMINENCE_CANDIDATES_PAGE_SLUG,
		'eminence_portal_render_candidates_page'
	);
}

/**
 * CSV export (FR-006) — runs on admin_init, before any HTML is sent, so it can send
 * download headers. Exports exactly the current filtered/sorted set, not the whole table.
 */
add_action( 'admin_init', 'eminence_portal_maybe_export_candidates_csv' );
function eminence_portal_maybe_export_candidates_csv() {
	if ( empty( $_GET['page'] ) || EMINENCE_CANDIDATES_PAGE_SLUG !== $_GET['page'] || empty( $_GET['eminence_export'] ) ) {
		return;
	}

	if ( ! current_user_can( EMINENCE_CAP_MANAGE_CANDIDATES ) ) {
		wp_die( esc_html__( 'You do not have permission to do that.', 'eminence-portal' ) );
	}

	$filters = eminence_portal_read_candidate_filters();
	$rows    = eminence_search_candidates( $filters, 1, 10000, eminence_portal_read_candidate_orderby(), eminence_portal_read_candidate_order() );

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="candidates-' . gmdate( 'Y-m-d' ) . '.csv"' );

	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, array( 'Name', 'Phone', 'Email', 'Experience (yrs)', 'Location', 'Department', 'Current CTC', 'Expected CTC', 'Notice Period', 'Client Name', 'Added By', 'Date Added' ) );

	foreach ( $rows as $row ) {
		$added_by = get_userdata( $row->added_by_user_id );
		fputcsv( $out, array(
			$row->candidate_name,
			$row->phone,
			$row->email,
			$row->total_experience_years,
			$row->current_location,
			$row->department,
			$row->current_ctc,
			$row->expected_ctc,
			$row->notice_period,
			$row->client_name,
			$added_by ? $added_by->display_name : '',
			$row->date_added,
		) );
	}

	fclose( $out );
	exit;
}

function eminence_portal_read_candidate_filters() {
	$get = wp_unslash( $_GET ); // phpcs-equivalent: read-only GET filters, each cast/sanitized individually below.

	return array(
		'name'            => isset( $get['name'] ) ? sanitize_text_field( $get['name'] ) : '',
		'department'      => isset( $get['department'] ) ? sanitize_text_field( $get['department'] ) : '',
		'location'        => isset( $get['location'] ) ? sanitize_text_field( $get['location'] ) : '',
		'experience_min'  => isset( $get['experience_min'] ) ? sanitize_text_field( $get['experience_min'] ) : '',
		'experience_max'  => isset( $get['experience_max'] ) ? sanitize_text_field( $get['experience_max'] ) : '',
		'ctc_min'         => isset( $get['ctc_min'] ) ? sanitize_text_field( $get['ctc_min'] ) : '',
		'ctc_max'         => isset( $get['ctc_max'] ) ? sanitize_text_field( $get['ctc_max'] ) : '',
		'notice_period'   => isset( $get['notice_period'] ) ? sanitize_text_field( $get['notice_period'] ) : '',
		'client_name'     => isset( $get['client_name'] ) ? sanitize_text_field( $get['client_name'] ) : '',
		'added_by'        => isset( $get['added_by'] ) ? absint( $get['added_by'] ) : '',
	);
}

function eminence_portal_read_candidate_orderby() {
	return isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'date_added';
}

function eminence_portal_read_candidate_order() {
	return ( isset( $_GET['order'] ) && 'asc' === strtolower( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) ) ? 'ASC' : 'DESC';
}

function eminence_portal_render_candidates_page() {
	if ( ! current_user_can( EMINENCE_CAP_MANAGE_CANDIDATES ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'eminence-portal' ) );
	}

	$delete_result = eminence_portal_handle_candidate_delete();

	$filters  = eminence_portal_read_candidate_filters();
	$orderby  = eminence_portal_read_candidate_orderby();
	$order    = eminence_portal_read_candidate_order();
	$page     = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
	$per_page = isset( $_GET['per_page'] ) ? absint( $_GET['per_page'] ) : 20;
	if ( ! in_array( $per_page, array( 20, 50, 100 ), true ) ) {
		$per_page = 20;
	}

	$results     = eminence_search_candidates( $filters, $page, $per_page, $orderby, $order );
	$total       = eminence_count_search_results( $filters );
	$total_pages = (int) ceil( $total / $per_page );

	$viewing = ! empty( $_GET['view'] ) ? eminence_get_candidate( absint( $_GET['view'] ) ) : null;
	?>
	<div class="wrap eminence-portal-screen">
		<h1><?php esc_html_e( 'Candidates', 'eminence-portal' ); ?></h1>

		<?php if ( $delete_result ) : ?>
			<div class="notice notice-<?php echo esc_attr( $delete_result['type'] ); ?>"><p><?php echo esc_html( $delete_result['message'] ); ?></p></div>
		<?php endif; ?>

		<?php if ( $viewing ) : ?>
			<div class="eminence-candidate-profile">
				<h2><?php echo esc_html( $viewing->candidate_name ); ?></h2>
				<table class="widefat">
					<?php
					$profile_rows = array(
						__( 'Phone', 'eminence-portal' )               => $viewing->phone,
						__( 'Email', 'eminence-portal' )               => $viewing->email,
						__( 'Current Location', 'eminence-portal' )    => $viewing->current_location,
						__( 'Total Experience', 'eminence-portal' )    => $viewing->total_experience_years,
						__( 'Current Company', 'eminence-portal' )     => $viewing->current_company,
						__( 'Current Designation', 'eminence-portal' ) => $viewing->current_designation,
						__( 'Department', 'eminence-portal' )          => $viewing->department,
						__( 'Current CTC', 'eminence-portal' )         => $viewing->current_ctc,
						__( 'Expected CTC', 'eminence-portal' )        => $viewing->expected_ctc,
						__( 'Notice Period', 'eminence-portal' )       => $viewing->notice_period,
						__( 'Preferred Location', 'eminence-portal' )  => $viewing->preferred_location,
						__( 'Client Name', 'eminence-portal' )         => $viewing->client_name,
						__( 'Position Name', 'eminence-portal' )       => $viewing->position_name,
						__( 'Source', 'eminence-portal' )              => $viewing->source,
						__( 'Remarks', 'eminence-portal' )             => $viewing->remarks,
					);
					foreach ( $profile_rows as $label => $value ) :
						?>
						<tr><th><?php echo esc_html( $label ); ?></th><td><?php echo esc_html( $value ); ?></td></tr>
					<?php endforeach; ?>
				</table>
				<?php if ( $viewing->cv_file_path ) : ?>
					<p><a class="button" href="<?php echo esc_url( eminence_cv_download_url( $viewing->id ) ); ?>"><?php esc_html_e( 'Download CV', 'eminence-portal' ); ?></a></p>
				<?php endif; ?>
				<p><a href="<?php echo esc_url( remove_query_arg( 'view' ) ); ?>">&larr; <?php esc_html_e( 'Back to results', 'eminence-portal' ); ?></a></p>
			</div>
		<?php endif; ?>

		<form method="get">
			<input type="hidden" name="page" value="<?php echo esc_attr( EMINENCE_CANDIDATES_PAGE_SLUG ); ?>" />
			<table class="form-table">
				<tr>
					<td><input type="text" name="name" placeholder="<?php esc_attr_e( 'Name', 'eminence-portal' ); ?>" value="<?php echo esc_attr( $filters['name'] ); ?>" /></td>
					<td>
						<select name="department">
							<option value=""><?php esc_html_e( 'Any department', 'eminence-portal' ); ?></option>
							<?php foreach ( eminence_candidate_department_options() as $option ) : ?>
								<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $filters['department'], $option ); ?>><?php echo esc_html( $option ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
					<td><input type="text" name="location" placeholder="<?php esc_attr_e( 'Location', 'eminence-portal' ); ?>" value="<?php echo esc_attr( $filters['location'] ); ?>" /></td>
					<td><input type="text" name="client_name" placeholder="<?php esc_attr_e( 'Client Name', 'eminence-portal' ); ?>" value="<?php echo esc_attr( $filters['client_name'] ); ?>" /></td>
				</tr>
				<tr>
					<td colspan="2">
						<?php esc_html_e( 'Experience (yrs):', 'eminence-portal' ); ?>
						<input type="number" step="0.1" name="experience_min" placeholder="<?php esc_attr_e( 'Min', 'eminence-portal' ); ?>" value="<?php echo esc_attr( $filters['experience_min'] ); ?>" class="small-text" />
						&ndash;
						<input type="number" step="0.1" name="experience_max" placeholder="<?php esc_attr_e( 'Max', 'eminence-portal' ); ?>" value="<?php echo esc_attr( $filters['experience_max'] ); ?>" class="small-text" />
					</td>
					<td colspan="2">
						<?php esc_html_e( 'CTC (LPA):', 'eminence-portal' ); ?>
						<input type="number" step="0.1" name="ctc_min" placeholder="<?php esc_attr_e( 'Min', 'eminence-portal' ); ?>" value="<?php echo esc_attr( $filters['ctc_min'] ); ?>" class="small-text" />
						&ndash;
						<input type="number" step="0.1" name="ctc_max" placeholder="<?php esc_attr_e( 'Max', 'eminence-portal' ); ?>" value="<?php echo esc_attr( $filters['ctc_max'] ); ?>" class="small-text" />
					</td>
				</tr>
				<tr>
					<td>
						<select name="notice_period">
							<option value=""><?php esc_html_e( 'Any notice period', 'eminence-portal' ); ?></option>
							<?php foreach ( eminence_candidate_notice_period_options() as $option ) : ?>
								<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $filters['notice_period'], $option ); ?>><?php echo esc_html( $option ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
					<td>
						<select name="added_by">
							<option value=""><?php esc_html_e( 'Anyone', 'eminence-portal' ); ?></option>
							<?php foreach ( eminence_portal_get_employee_accounts() as $employee ) : ?>
								<option value="<?php echo esc_attr( $employee->ID ); ?>" <?php selected( (string) $filters['added_by'], (string) $employee->ID ); ?>><?php echo esc_html( $employee->display_name ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
					<td colspan="2">
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Search', 'eminence-portal' ); ?></button>
						<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . EMINENCE_CANDIDATES_PAGE_SLUG ) ); ?>"><?php esc_html_e( 'Reset', 'eminence-portal' ); ?></a>
						<a class="button" href="<?php echo esc_url( add_query_arg( 'eminence_export', 'csv' ) ); ?>"><?php esc_html_e( 'Export CSV', 'eminence-portal' ); ?></a>
					</td>
				</tr>
			</table>
		</form>

		<p>
			<?php
			printf(
				/* translators: %d: number of matching candidates */
				esc_html( _n( '%d result', '%d results', $total, 'eminence-portal' ) ),
				(int) $total
			);
			?>
			&mdash;
			<?php foreach ( array( 20, 50, 100 ) as $size ) : ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'per_page' => $size, 'paged' => 1 ) ) ); ?>" <?php echo ( $per_page === $size ) ? 'style="font-weight:bold"' : ''; ?>><?php echo esc_html( $size ); ?></a>
			<?php endforeach; ?>
			<?php esc_html_e( 'per page', 'eminence-portal' ); ?>
		</p>

		<table class="widefat striped">
			<thead>
				<tr>
					<?php
					$columns = array(
						'candidate_name'         => __( 'Name', 'eminence-portal' ),
						'total_experience_years' => __( 'Experience', 'eminence-portal' ),
						'current_location'       => __( 'Location', 'eminence-portal' ),
						'current_ctc'            => __( 'CTC', 'eminence-portal' ),
					);
					foreach ( $columns as $column => $label ) :
						$next_order = ( $orderby === $column && 'ASC' === $order ) ? 'desc' : 'asc';
						?>
						<th><a href="<?php echo esc_url( add_query_arg( array( 'orderby' => $column, 'order' => $next_order ) ) ); ?>"><?php echo esc_html( $label ); ?></a></th>
					<?php endforeach; ?>
					<th><?php esc_html_e( 'Department', 'eminence-portal' ); ?></th>
					<th><?php esc_html_e( 'Added By', 'eminence-portal' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'eminence-portal' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! $results ) : ?>
					<tr><td colspan="7"><?php esc_html_e( 'No candidates match these filters.', 'eminence-portal' ); ?></td></tr>
				<?php endif; ?>
				<?php foreach ( $results as $candidate ) : ?>
					<?php $added_by = get_userdata( $candidate->added_by_user_id ); ?>
					<tr>
						<td><?php echo esc_html( $candidate->candidate_name ); ?></td>
						<td><?php echo esc_html( $candidate->total_experience_years ); ?></td>
						<td><?php echo esc_html( $candidate->current_location ); ?></td>
						<td><?php echo esc_html( $candidate->current_ctc ? $candidate->current_ctc : $candidate->expected_ctc ); ?></td>
						<td><?php echo esc_html( $candidate->department ); ?></td>
						<td><?php echo esc_html( $added_by ? $added_by->display_name : '' ); ?></td>
						<td>
							<a href="<?php echo esc_url( add_query_arg( 'view', $candidate->id ) ); ?>"><?php esc_html_e( 'View', 'eminence-portal' ); ?></a>
							<?php if ( $candidate->cv_file_path ) : ?>
								| <a href="<?php echo esc_url( eminence_cv_download_url( $candidate->id ) ); ?>"><?php esc_html_e( 'Download CV', 'eminence-portal' ); ?></a>
							<?php endif; ?>
							<?php if ( eminence_can_edit_candidate( $candidate ) ) : ?>
								| <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . EMINENCE_ADD_CANDIDATE_PAGE_SLUG . '&candidate_id=' . $candidate->id ) ); ?>"><?php esc_html_e( 'Edit', 'eminence-portal' ); ?></a>
								| <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'eminence_delete_candidate', $candidate->id ), EMINENCE_CANDIDATE_DELETE_NONCE_ACTION ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete this candidate permanently?', 'eminence-portal' ) ); ?>');"><?php esc_html_e( 'Delete', 'eminence-portal' ); ?></a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( $total_pages > 1 ) : ?>
			<p class="tablenav-pages">
				<?php for ( $i = 1; $i <= $total_pages; $i++ ) : ?>
					<a href="<?php echo esc_url( add_query_arg( 'paged', $i ) ); ?>" <?php echo ( $i === $page ) ? 'style="font-weight:bold"' : ''; ?>><?php echo esc_html( $i ); ?></a>
				<?php endfor; ?>
			</p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * @return array|null Feedback if a delete was processed, or null.
 */
function eminence_portal_handle_candidate_delete() {
	if ( empty( $_GET['eminence_delete_candidate'] ) ) {
		return null;
	}

	if (
		! isset( $_GET['_wpnonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), EMINENCE_CANDIDATE_DELETE_NONCE_ACTION )
	) {
		return array( 'type' => 'error', 'message' => __( 'Security check failed — please try again.', 'eminence-portal' ) );
	}

	$candidate = eminence_get_candidate( absint( $_GET['eminence_delete_candidate'] ) );

	if ( ! $candidate || ! eminence_can_edit_candidate( $candidate ) ) {
		return array( 'type' => 'error', 'message' => __( 'You do not have permission to delete this candidate.', 'eminence-portal' ) );
	}

	global $wpdb;
	if ( $candidate->cv_file_path ) {
		$path = eminence_cv_absolute_path( $candidate->cv_file_path );
		if ( $path && file_exists( $path ) ) {
			wp_delete_file( $path );
		}
	}
	$wpdb->delete( eminence_candidates_table(), array( 'id' => $candidate->id ) );

	return array( 'type' => 'success', 'message' => __( 'Candidate deleted.', 'eminence-portal' ) );
}

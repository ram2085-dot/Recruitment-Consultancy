<?php
/**
 * All $wpdb reads/writes for the candidate database — the one place SQL for this feature
 * lives (research.md #5), so duplicate detection (Principle IV) and the "active means
 * searchable" rule (Principle III) each have exactly one implementation to get right.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EMINENCE_CANDIDATE_STATUS_ACTIVE', 'active' );
define( 'EMINENCE_CANDIDATE_STATUS_PENDING', 'pending_review' );
define( 'EMINENCE_CANDIDATE_STATUS_REJECTED', 'archived_rejected' );

define( 'EMINENCE_RETENTION_MONTHS', 24 );

/**
 * Columns a caller (add-candidate form, review-approval form, edit form) may set directly.
 * Deliberately excludes id, added_by_user_id, date_added, status, the reviewed_/reject_
 * columns, last_activity_at, created_at, and updated_at — those are only ever set by the
 * functions below, never passed through from form input (FR-003: auto-captured, never
 * client-submitted).
 */
function eminence_candidate_editable_columns() {
	return array(
		'client_name',
		'position_name',
		'profile_shared_on',
		'candidate_name',
		'phone',
		'email',
		'current_location',
		'total_experience_years',
		'current_company',
		'current_designation',
		'department',
		'current_ctc',
		'expected_ctc',
		'notice_period',
		'preferred_location',
		'cv_file_path',
		'source',
		'remarks',
	);
}

/**
 * Insert a new candidate record.
 *
 * @param array  $fields           Associative array of column => value (editable columns only).
 * @param string $status           One of the EMINENCE_CANDIDATE_STATUS_* constants.
 * @param int    $added_by_user_id WP user ID — always the current employee, never trusted from input.
 * @return int Inserted row ID.
 */
function eminence_insert_candidate( array $fields, $status, $added_by_user_id ) {
	global $wpdb;

	$now  = current_time( 'mysql' );
	$data = array();

	foreach ( eminence_candidate_editable_columns() as $column ) {
		if ( array_key_exists( $column, $fields ) ) {
			$data[ $column ] = $fields[ $column ];
		}
	}

	$data['added_by_user_id'] = (int) $added_by_user_id;
	$data['date_added']       = $now;
	$data['status']           = $status;
	$data['last_activity_at'] = $now;
	$data['created_at']       = $now;
	$data['updated_at']       = $now;

	$wpdb->insert( eminence_candidates_table(), $data );

	return (int) $wpdb->insert_id;
}

/**
 * Update an existing candidate's editable fields (used by both the review-approval form
 * and any future edit screen). Always refreshes last_activity_at (constitution Principle
 * VI — retention counts from last update, not just creation).
 */
function eminence_update_candidate( $id, array $fields ) {
	global $wpdb;

	$data = array();
	foreach ( eminence_candidate_editable_columns() as $column ) {
		if ( array_key_exists( $column, $fields ) ) {
			$data[ $column ] = $fields[ $column ];
		}
	}

	if ( ! $data ) {
		return false;
	}

	$now                      = current_time( 'mysql' );
	$data['updated_at']       = $now;
	$data['last_activity_at'] = $now;

	return false !== $wpdb->update( eminence_candidates_table(), $data, array( 'id' => (int) $id ) );
}

/**
 * Approve or reject a candidate record — the only two state transitions out of
 * pending_review (data-model.md). Always stamps who and when.
 */
function eminence_set_candidate_status( $id, $status, $reviewer_id, $reject_reason = null ) {
	global $wpdb;

	$now = current_time( 'mysql' );

	return false !== $wpdb->update(
		eminence_candidates_table(),
		array(
			'status'              => $status,
			'reviewed_by_user_id' => (int) $reviewer_id,
			'reviewed_at'         => $now,
			'reject_reason'       => $reject_reason,
			'last_activity_at'    => $now,
			'updated_at'          => $now,
		),
		array( 'id' => (int) $id )
	);
}

/**
 * The one canonical duplicate check (research.md #5, constitution Principle IV) — called
 * from both the direct-add path and the review-approval path. Matches on phone OR email
 * (either alone is sufficient, BRD 6.3), against every status including
 * archived_rejected — a previously-rejected person re-submitting should surface that
 * history, not look like a first-time submission.
 *
 * @param string $phone
 * @param string $email
 * @param int    $exclude_id Row ID to exclude from the match — required at review time
 *                            (candidate-review.php), where the pending submission being
 *                            reviewed is ALREADY a row in this table with this same
 *                            phone/email, and would otherwise match itself and hide any
 *                            real duplicate behind the LIMIT 1. Unused at add-time
 *                            (candidate-form.php), where the record doesn't exist yet.
 * @return object|null The most recent OTHER matching row, or null if no match.
 */
function eminence_find_duplicate_candidate( $phone, $email, $exclude_id = 0 ) {
	global $wpdb;

	$phone = trim( (string) $phone );
	$email = trim( (string) $email );

	$conditions = array();
	$params     = array();

	if ( '' !== $phone ) {
		$conditions[] = 'phone = %s';
		$params[]     = $phone;
	}

	if ( '' !== $email ) {
		$conditions[] = 'email = %s';
		$params[]     = $email;
	}

	if ( ! $conditions ) {
		return null;
	}

	$where = '(' . implode( ' OR ', $conditions ) . ')';

	if ( $exclude_id ) {
		$where   .= ' AND id != %d';
		$params[] = (int) $exclude_id;
	}

	$table = eminence_candidates_table();
	$sql   = "SELECT * FROM $table WHERE $where ORDER BY id DESC LIMIT 1";

	$row = $wpdb->get_row( $wpdb->prepare( $sql, $params ) );

	return $row ? $row : null;
}

/**
 * @param int $id Candidate row ID.
 * @return object|null
 */
function eminence_get_candidate( $id ) {
	global $wpdb;
	$table = eminence_candidates_table();

	return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", (int) $id ) );
}

/**
 * Column allow-list for ORDER BY — column names can't go through $wpdb->prepare()'s
 * placeholders, so this whitelist is what keeps sortable-column input safe.
 */
function eminence_candidate_sortable_columns() {
	return array( 'candidate_name', 'total_experience_years', 'current_location', 'current_ctc', 'expected_ctc', 'date_added' );
}

/**
 * Builds the shared WHERE clause + params for both eminence_search_candidates() and
 * eminence_count_search_results() — one filter-building implementation, not two that
 * could drift apart (FR-005).
 */
function eminence_build_candidate_where( array $filters ) {
	$where  = array( 'status = %s' );
	$params = array( EMINENCE_CANDIDATE_STATUS_ACTIVE );

	if ( ! empty( $filters['name'] ) ) {
		global $wpdb;
		$where[]  = 'candidate_name LIKE %s';
		$params[] = '%' . $wpdb->esc_like( $filters['name'] ) . '%';
	}

	if ( ! empty( $filters['department'] ) ) {
		$where[]  = 'department = %s';
		$params[] = $filters['department'];
	}

	if ( ! empty( $filters['location'] ) ) {
		$where[]  = 'current_location = %s';
		$params[] = $filters['location'];
	}

	if ( isset( $filters['experience_min'] ) && '' !== $filters['experience_min'] ) {
		$where[]  = 'total_experience_years >= %f';
		$params[] = (float) $filters['experience_min'];
	}

	if ( isset( $filters['experience_max'] ) && '' !== $filters['experience_max'] ) {
		$where[]  = 'total_experience_years <= %f';
		$params[] = (float) $filters['experience_max'];
	}

	// CTC range matches either current or expected CTC — a Recruiter searching "8-12 LPA"
	// wants candidates in that band whichever CTC figure applies to the search intent.
	if ( isset( $filters['ctc_min'] ) && '' !== $filters['ctc_min'] ) {
		$where[]  = '(current_ctc >= %f OR expected_ctc >= %f)';
		$params[] = (float) $filters['ctc_min'];
		$params[] = (float) $filters['ctc_min'];
	}

	if ( isset( $filters['ctc_max'] ) && '' !== $filters['ctc_max'] ) {
		$where[]  = '(current_ctc <= %f OR expected_ctc <= %f)';
		$params[] = (float) $filters['ctc_max'];
		$params[] = (float) $filters['ctc_max'];
	}

	if ( ! empty( $filters['notice_period'] ) ) {
		$where[]  = 'notice_period = %s';
		$params[] = $filters['notice_period'];
	}

	if ( ! empty( $filters['client_name'] ) ) {
		$where[]  = 'client_name = %s';
		$params[] = $filters['client_name'];
	}

	if ( ! empty( $filters['added_by'] ) ) {
		$where[]  = 'added_by_user_id = %d';
		$params[] = (int) $filters['added_by'];
	}

	return array( implode( ' AND ', $where ), $params );
}

/**
 * Filtered, sorted, paginated candidate search (FR-005, FR-006, FR-007). Always scoped to
 * status = active — this is what makes FR-009 ("a pending submission never appears in
 * search") true by construction rather than an extra check to remember.
 */
function eminence_search_candidates( array $filters, $page = 1, $per_page = 20, $orderby = 'date_added', $order = 'DESC' ) {
	global $wpdb;

	list( $where_sql, $params ) = eminence_build_candidate_where( $filters );

	$orderby = in_array( $orderby, eminence_candidate_sortable_columns(), true ) ? $orderby : 'date_added';
	$order   = ( 'ASC' === strtoupper( $order ) ) ? 'ASC' : 'DESC';
	$offset  = max( 0, ( (int) $page - 1 ) * (int) $per_page );

	$table      = eminence_candidates_table();
	$sql        = "SELECT * FROM $table WHERE $where_sql ORDER BY $orderby $order LIMIT %d OFFSET %d";
	$params[]   = (int) $per_page;
	$params[]   = $offset;

	return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
}

/**
 * Total matching rows for $filters, ignoring pagination — for the results table's page count.
 */
function eminence_count_search_results( array $filters ) {
	global $wpdb;

	list( $where_sql, $params ) = eminence_build_candidate_where( $filters );

	$table = eminence_candidates_table();
	$sql   = "SELECT COUNT(*) FROM $table WHERE $where_sql";

	return (int) $wpdb->get_var( $wpdb->prepare( $sql, $params ) );
}

/**
 * @param string $status One of the EMINENCE_CANDIDATE_STATUS_* constants.
 * @return int
 */
function eminence_count_candidates_by_status( $status ) {
	global $wpdb;
	$table = eminence_candidates_table();

	return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE status = %s", $status ) );
}

/**
 * All pending-review candidates, oldest first (so nothing sits unreviewed indefinitely —
 * FR-013).
 */
function eminence_get_pending_candidates() {
	global $wpdb;
	$table = eminence_candidates_table();

	return $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM $table WHERE status = %s ORDER BY date_added ASC", EMINENCE_CANDIDATE_STATUS_PENDING )
	);
}

/**
 * Ownership check (FR-004): the employee who added a record can edit it; an Admin
 * (EMINENCE_CAP_EDIT_ANY_CANDIDATE) can edit any record.
 *
 * @param object $candidate Row from eminence_get_candidate() or a search result.
 * @return bool
 */
function eminence_can_edit_candidate( $candidate ) {
	if ( current_user_can( EMINENCE_CAP_EDIT_ANY_CANDIDATE ) ) {
		return true;
	}

	return (int) $candidate->added_by_user_id === get_current_user_id();
}

/**
 * Deletes every candidate record (any status) whose last_activity_at is older than the
 * retention window (constitution Principle VI, FR-017 — archived/rejected records are
 * NOT exempt) and its CV file, if any. Scheduled daily — see
 * eminence_portal_schedule_retention_sweep() below.
 *
 * @return int Number of records deleted.
 */
function eminence_delete_expired_candidates() {
	global $wpdb;
	$table = eminence_candidates_table();

	$cutoff = gmdate( 'Y-m-d H:i:s', strtotime( '-' . EMINENCE_RETENTION_MONTHS . ' months' ) );

	$expired = $wpdb->get_results( $wpdb->prepare( "SELECT id, cv_file_path FROM $table WHERE last_activity_at < %s", $cutoff ) );

	if ( ! $expired ) {
		return 0;
	}

	foreach ( $expired as $row ) {
		if ( ! empty( $row->cv_file_path ) && function_exists( 'eminence_cv_absolute_path' ) ) {
			$path = eminence_cv_absolute_path( $row->cv_file_path );
			if ( $path && file_exists( $path ) ) {
				wp_delete_file( $path );
			}
		}
	}

	$ids = array_map( 'intval', wp_list_pluck( $expired, 'id' ) );
	$wpdb->query( "DELETE FROM $table WHERE id IN (" . implode( ',', $ids ) . ')' );

	return count( $expired );
}

add_action( 'eminence_portal_daily_retention_sweep', 'eminence_delete_expired_candidates' );

/**
 * Schedules the daily retention sweep — called from activation (roles.php) and from the
 * upgrade check (candidates-schema.php), same reasoning as the capability retrofit: an
 * already-active install needs this scheduled too, not just a fresh one.
 */
function eminence_portal_schedule_retention_sweep() {
	if ( ! wp_next_scheduled( 'eminence_portal_daily_retention_sweep' ) ) {
		wp_schedule_event( time(), 'daily', 'eminence_portal_daily_retention_sweep' );
	}
}

# Phase 1 Data Model: Candidate Database, CV Upload & Review Workflow

One new table. No postmeta, no new CPT (research.md #1).

## `{$wpdb->prefix}eminence_candidates`

| Column | Type | BRD 6.2 # | Notes |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY` | — | |
| `client_name` | `VARCHAR(190)` | 1 | Nullable — not applicable to a public submission until reviewed. |
| `position_name` | `VARCHAR(190)` | 2 | Nullable, same reason. |
| `profile_shared_on` | `DATE` | 3 | Nullable, same reason. |
| `candidate_name` | `VARCHAR(190) NOT NULL` | 4 | Mandatory at every entry point (FR-001, FR-008). |
| `phone` | `VARCHAR(20) NOT NULL, INDEX` | 5 | Mandatory. Indexed — duplicate lookup (research.md #5) and search both filter on it. |
| `email` | `VARCHAR(190) NOT NULL, INDEX` | 6 | Mandatory. Indexed, same reason. |
| `current_location` | `VARCHAR(190), INDEX` | 7 | Indexed — a search filter (FR-005). |
| `total_experience_years` | `DECIMAL(4,1) NOT NULL, INDEX` | 8 | Mandatory. Indexed — range filter (FR-005). |
| `current_company` | `VARCHAR(190)` | 9 | |
| `current_designation` | `VARCHAR(190)` | 10 | |
| `department` | `VARCHAR(100) NOT NULL, INDEX` | 11 | Mandatory. Indexed — search filter and the dashboard's per-department breadth. |
| `current_ctc` | `DECIMAL(8,2), INDEX` | 12 | LPA. Indexed — range filter. |
| `expected_ctc` | `DECIMAL(8,2), INDEX` | 13 | LPA. Indexed — range filter. |
| `notice_period` | `VARCHAR(30), INDEX` | 14 | One of Immediate/15/30/60/90 days — enforced in code, not a DB enum (keeps future value changes a code change, not a migration). Indexed — search filter. |
| `preferred_location` | `VARCHAR(190)` | 15 | Free text/multi-value stored as a delimited string — no filtering requirement on this field in FR-005, so no index. |
| `cv_file_path` | `VARCHAR(255)` | 16 | Server-relative path under the private upload directory (research.md #3), never a public URL. Nullable — only omissible by an Admin adding a historical record (FR-001). |
| `added_by_user_id` | `BIGINT UNSIGNED NOT NULL, INDEX` | 17 | WP user ID, auto-set server-side (FR-003) — never client-submitted. Indexed — "search by added-by" (FR-005). |
| `date_added` | `DATETIME NOT NULL` | 18 | Auto-set server-side (FR-003). |
| `source` | `VARCHAR(50)` | 19 | Naukri / Reference / LinkedIn / Other / **Website** (BRD 6.6 step 5 — set automatically to "Website" for records approved from a public submission). |
| `remarks` | `TEXT` | 20 | |
| `status` | `VARCHAR(20) NOT NULL, INDEX` | — | `active` \| `pending_review` \| `archived_rejected` (research.md #2). Every search query filters on this (FR-009). |
| `reviewed_by_user_id` | `BIGINT UNSIGNED` | — | WP user ID of the Recruiter/Admin who Approved or Rejected. Null while `pending_review`. |
| `reviewed_at` | `DATETIME` | — | Null while `pending_review`. |
| `reject_reason` | `VARCHAR(30)` | — | Duplicate / Incomplete / Not Relevant / Spam / null. Only meaningful when `status = archived_rejected`. |
| `last_activity_at` | `DATETIME NOT NULL` | — | Drives the 24-month retention rule (constitution Principle VI, FR-017) — updated whenever the record is created or edited. Not a BRD-6.2 field; introduced to make retention enforceable. |
| `created_at` / `updated_at` | `DATETIME` | — | Standard bookkeeping. |

**Mandatory fields** (enforced in code before any `INSERT`, per FR-001/BRD 6.3):
`candidate_name`, `phone`, `email`, `total_experience_years`, `department`,
and `cv_file_path` — except `cv_file_path` may be null when the record is
created by an `eminence_portal_admin` account explicitly marked as a
historical/legacy entry (a boolean flag on the add-candidate form, not a
stored column — it only ever affects whether the mandatory-field check is
enforced at submit time).

A `pending_review` row from the public form only ever has
`candidate_name`, `total_experience_years`, `current_location`,
`expected_ctc` (BRD 7.1 calls this "current CTC" on the public form, but
6.6 step 5 has the reviewing employee fill in the rest — a submitter has no
"current" CTC context to give without more fields than the public form asks
for, so the single public CTC value is stored as `expected_ctc` and
`current_ctc` stays null until review), `department`, `phone`, `email`, and
the auto fields (`status = pending_review`, `date_added`,
`last_activity_at`) populated — every other column is null until an
employee fills it in during review (FR-010).

## State transitions (`status`)

```text
(direct add, User Story 1)          -> active
(public submission, User Story 3)   -> pending_review
pending_review -> active            [Approve; sets reviewed_by/reviewed_at, source stays
                                      whatever the reviewer entered, or "Website" if left blank]
pending_review -> archived_rejected [Reject; sets reviewed_by/reviewed_at/reject_reason]
```

There is no `active -> pending_review` or any other transition — once a
record is `active` or `archived_rejected`, it stays there (edits change its
fields, per FR-004, not its status) until retention deletes it (FR-017).

## Indexes summary

`phone`, `email` (duplicate detection + search), `department`,
`current_location`, `total_experience_years`, `current_ctc`,
`expected_ctc`, `notice_period`, `added_by_user_id`, `status` — every column
FR-005's search filters or FR-002's duplicate check touches. At ≤10,000 rows
this keeps every filter combination a fast indexed lookup rather than a
table scan (supports SC-003).

## Relationship to existing entities (011-employee-login)

- `added_by_user_id` / `reviewed_by_user_id` reference the same WP user IDs
  as 011's Employee Account entity — no duplication of who-is-who, just a
  foreign key by convention (WordPress core tables aren't set up for real
  `FOREIGN KEY` constraints across a plugin's custom table by default, so
  this is enforced in application code, not the schema).
- The Dashboard (User Story 4) reads: `COUNT(*)` of active employee accounts
  (011's existing `eminence_portal_get_employee_accounts()`), `COUNT(*)
  WHERE status = 'active'` and `WHERE status = 'pending_review'` from this
  table, and 011's existing sign-in log for "most recently signed in" — no
  new entity needed for the dashboard itself, it's a read-only aggregation
  over data that already exists once User Stories 1-3 are built.

# Tasks: Candidate Database, CV Upload & Review Workflow

**Input**: Design documents from `/specs/012-candidate-database/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/candidate-data-contract.md, quickstart.md

**Tests**: No automated test framework applies (see plan.md Testing) — verification is the
scripted WP-CLI/curl checks in quickstart.md, including a synthetic-data scale test, tracked
as explicit tasks below rather than a separate `tests/` phase.

**Organization**: Tasks are grouped by the four user stories in spec.md (US1-US4, priority
order P1-P4). All paths are relative to the repository root, inside the existing
`eminence-portal` plugin (011-employee-login) unless noted.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: US1 = Recruiter adds a profile; US2 = search/filter; US3 = public submission +
  review; US4 = Dashboard

---

## Phase 1: Setup

**Purpose**: Confirm the base this feature extends is actually in place

- [ ] T001 Verify `eminence-portal` (011-employee-login) is active and both roles
  (`eminence_recruiter`, `eminence_portal_admin`) exist — `wp plugin list` / `wp role list` —
  before extending it; this feature adds no new plugin, just new files inside the existing one

**Checkpoint**: Confirmed base is in place; nothing new to scaffold.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Schema, capabilities, and the shared data-access layer every user story needs

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [ ] T002 Create `wp-content/plugins/eminence-portal/includes/candidates-schema.php`: `dbDelta()` creation of `{$wpdb->prefix}eminence_candidates` per data-model.md's full column/index list, hooked onto the plugin's existing activation function
- [ ] T003 In `includes/capabilities.php`, add `EMINENCE_CAP_MANAGE_CANDIDATES` and `EMINENCE_CAP_EDIT_ANY_CANDIDATE` constants (research.md #6)
- [ ] T004 In `includes/roles.php`, retrofit the two new capabilities onto the already-existing roles via `get_role( ... )->add_cap( ... )` run on an upgrade check (not just the activation hook — the roles already exist from 011, and `add_role()` does not update an already-existing role's capabilities): `eminence_manage_candidates` to both `eminence_recruiter` and `eminence_portal_admin`; `eminence_edit_any_candidate` to `eminence_portal_admin` only
- [ ] T005 Create `wp-content/plugins/eminence-portal/includes/candidate-repository.php`: `eminence_insert_candidate()`, `eminence_find_duplicate_candidate( $phone, $email )` (research.md #5, matches against every status including `archived_rejected`), `eminence_search_candidates( $filters, $page, $per_page, $orderby )`, `eminence_count_candidates_by_status( $status )`, `eminence_get_candidate( $id )`, `eminence_update_candidate( $id, $fields )`, `eminence_set_candidate_status( $id, $status, $reviewer_id, $reject_reason = null )`, and `eminence_can_edit_candidate( $candidate )` (ownership check: added-by match OR `eminence_edit_any_candidate` — FR-004) — the one place SQL for this feature lives
- [ ] T006 [P] In `candidate-repository.php`, add `eminence_delete_expired_candidates()` (deletes any record, any status, where `last_activity_at` is older than 24 months — constitution Principle VI, FR-017) and schedule it daily via `wp_schedule_event()` on activation — this is a base guarantee the whole feature must provide, not a later concern
- [ ] T007 [P] Create `wp-content/plugins/eminence-portal/includes/cv-storage.php`: upload validation (PDF/DOC/DOCX only, max 5MB, checked server-side not just via HTML attributes), a private (non-public-URL) storage path helper, and the capability-gated download endpoint (contracts/candidate-data-contract.md — query-var handler checking `eminence_manage_candidates` + nonce before ever resolving a file path)
- [ ] T008 Wire `eminence-portal.php` to `require` `candidates-schema.php`, `candidate-repository.php`, and `cv-storage.php`, and hook `candidates-schema.php`'s table creation + `candidate-repository.php`'s retention-sweep scheduling into the plugin's activation function (depends on T002-T007)

**Checkpoint**: Table exists, capabilities exist, the shared repository and CV-storage layer are ready — every user story can now be built on top of this.

---

## Phase 3: User Story 1 - Recruiter adds a candidate profile (Priority: P1) 🎯 MVP

**Goal**: A Recruiter or Admin can add a candidate profile with CV, blocked by a duplicate
check, with the historical-record exception for Admins, and ownership enforced.

**Independent Test**: Add one profile with a new phone/email and confirm it saves; add a
second with the same phone number and confirm the duplicate warning blocks it — quickstart.md
Scenarios 1-5.

### Implementation for User Story 1

- [ ] T009 [US1] Create `wp-content/plugins/eminence-portal/includes/candidate-form.php`: wp-admin "Add Candidate" screen with the full BRD-6.2 field form, mandatory-field validation (FR-001), and a "historical record" checkbox (Admin-only, waives the CV-required rule)
- [ ] T010 [US1] In `candidate-form.php`, call `eminence_find_duplicate_candidate()` (T005) before insert; on a match, render the existing profile side-by-side instead of saving (FR-002)
- [ ] T011 [US1] In `candidate-form.php`, call `cv-storage.php`'s upload handler (T007) and `eminence_insert_candidate()` (T005) on submit, auto-setting `added_by_user_id`/`date_added`/`last_activity_at` server-side (FR-003) — never from form input
- [ ] T012 [US1] Register the Add Candidate page gated on `eminence_manage_candidates` (depends on T003)

**Checkpoint**: User Story 1 fully functional and independently testable — quickstart.md Scenarios 1-5 pass.

---

## Phase 4: User Story 2 - Recruiter searches and filters candidates (Priority: P2)

**Goal**: Filter/search the database by any combination of the spec's fields, with a
sortable, paginated results table, CV download, and CSV export.

**Independent Test**: With a few profiles in the system, filter by one field and confirm
only matches appear; combine two filters and confirm results narrow — quickstart.md
Scenarios 6-11.

### Implementation for User Story 2

- [ ] T013 [US2] Create `wp-content/plugins/eminence-portal/includes/candidate-search.php`: wp-admin "Candidates" screen — filter form (department, experience range, location, CTC range, notice period, client name, added-by) calling `eminence_search_candidates()` (T005), results table (Name, Experience, Location, CTC), sortable columns (FR-005, FR-006)
- [ ] T014 [US2] In `candidate-search.php`, wire pagination with a 20/50/100-per-page choice (FR-007)
- [ ] T015 [US2] In `candidate-search.php`, add a View Profile action (full record) and a CV Download link routed through `cv-storage.php`'s endpoint (T007), never a direct file URL
- [ ] T016 [US2] In `candidate-search.php`, add "Export results" generating a CSV of the current filtered/sorted set via `fputcsv()` (research.md #4, FR-006)
- [ ] T017 [US2] In `candidate-search.php`'s results table, show/hide Edit and Delete per row using `eminence_can_edit_candidate()` (T005) — satisfies FR-004 on the search screen, not just the add screen

**Checkpoint**: User Stories 1 AND 2 both work independently — quickstart.md Scenarios 1-11 pass.

---

## Phase 5: User Story 3 - Public submission + review workflow (Priority: P3)

**Goal**: A visitor submits the 5-field public form; it lands in Pending Review, never in
search results, until a Recruiter/Admin approves or rejects it.

**Independent Test**: Submit the public form, confirm it's absent from every search filter
combination from User Story 2; approve it and confirm it now appears — quickstart.md
Scenarios 12-16.

### Implementation for User Story 3

- [ ] T018 [US3] Create `wp-content/plugins/eminence-portal/includes/public-cv-form.php`: register `[eminence_cv_submission]` — a form capturing Name, Phone, Email, Experience, Location, CTC, Department (FR-008 — Phone/Email added to the BRD's original "5 fields" so the mandatory duplicate check has something to match on, see spec.md Assumptions), no login required
- [ ] T019 [US3] In `public-cv-form.php`, handle submission: `eminence_insert_candidate()` (T005) with `status = pending_review`, show an on-page confirmation, send no email/notification (FR-016)
- [ ] T020 [US3] Insert `[eminence_cv_submission]` into the existing For Candidates page's content via `wp post update` (content edit, research.md #8 — not a template code change; verify against 006-for-candidates-page's current content first)
- [ ] T021 [US3] Create `wp-content/plugins/eminence-portal/includes/candidate-review.php`: wp-admin "Pending Review" screen listing every `status = pending_review` record (FR-010)
- [ ] T022 [US3] In `candidate-review.php`, run `eminence_find_duplicate_candidate()` (T005) when a pending record is opened and display any match side-by-side (FR-002 applies here too, not just User Story 1)
- [ ] T023 [US3] In `candidate-review.php`, build the review form (the 15 fields the public form didn't capture) + an Approve handler calling `eminence_set_candidate_status( $id, 'active', $reviewer_id )` (defaults `source` to "Website" if the reviewer leaves it blank) (FR-011)
- [ ] T024 [US3] In `candidate-review.php`, add a Reject handler calling `eminence_set_candidate_status( $id, 'archived_rejected', $reviewer_id, $reason )` with an optional reason dropdown (Duplicate/Incomplete/Not Relevant/Spam) (FR-012)
- [ ] T025 [US3] Add a pending-count badge (WordPress's native admin-menu bubble) on the Pending Review menu item, visible from anywhere in wp-admin (FR-013)

**Checkpoint**: User Stories 1-3 all work independently — quickstart.md Scenarios 1-16 pass.

---

## Phase 6: User Story 4 - Dashboard shows portal-wide counts (Priority: P4)

**Goal**: A single-glance dashboard — employee count, active-CV count, pending-review count,
recent logins — that a Portal Admin now lands on immediately after signing in.

**Independent Test**: With a known number of employees/active profiles/pending submissions,
load the dashboard and confirm every count matches — quickstart.md Scenarios 17-20.

### Implementation for User Story 4

- [ ] T026 [US4] Create `wp-content/plugins/eminence-portal/includes/dashboard.php`: wp-admin "Dashboard" screen with 4 cards — Employee Count (011's `eminence_portal_get_employee_accounts()`), Active CVs (`eminence_count_candidates_by_status( 'active' )`, T005), Pending Review (`eminence_count_candidates_by_status( 'pending_review' )`, matches T025's badge exactly), and a Recent Logins list (011's sign-in log)
- [ ] T027 [US4] In `includes/account-management.php`, restructure the `admin_menu` registration into a group: Dashboard (default), Employee Accounts (`eminence_manage_employees`-gated, unchanged behavior), Candidates, Add Candidate, Pending Review (all `eminence_manage_candidates`-gated) (research.md #7)
- [ ] T028 [US4] In `includes/auth.php`, change the Admin post-login redirect target from the Employee Accounts URL to the new Dashboard URL (research.md #7 — this is what actually finishes "admin shall land on his dashboard")

**Checkpoint**: All four user stories independently functional — quickstart.md Scenarios 1-20 pass.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Verification sweep, including the scale/performance target

- [X] T029 Run every scenario in quickstart.md end-to-end against the real local WordPress install (junctioned into the preview, upgrade hook verified to retrofit the table + capabilities onto the already-active 011 install). All scenarios pass — see the two bugs found and fixed below.
- [X] T030 [P] Run the "duplicate-detection sweep" — confirmed both entry points: direct-add (candidate-form.php) blocks on a matching phone with the existing profile shown side-by-side; review-time (candidate-review.php) flags the same collision. **Bug found and fixed**: the review-time check originally called the shared `eminence_find_duplicate_candidate()` without excluding the submission's own row — since a pending record already has its own phone/email in the table, `ORDER BY id DESC LIMIT 1` matched the submission against *itself* and never reached the real duplicate. Added an `$exclude_id` parameter to the shared function (still one canonical implementation, research.md #5) and passed the submission's own ID at review time; direct-add doesn't pass it (the record doesn't exist yet).
- [X] T031 [P] Run the "CV access-denial sweep" — no session: 403. Valid Recruiter/Admin session + valid ID: succeeds with correct filename via Content-Disposition. Confirmed CV download does NOT require ownership (any employee can download any active candidate's CV, matching FR-004's scope — only edit/delete are ownership-gated, not viewing/downloading).
- [X] T032 [P] Run the "public-submission isolation check" — a fresh public submission (status `pending_review`) confirmed absent from search results (all filter combos scoped to `status = active` structurally, FR-009) until Approved, at which point it appeared immediately.
- [X] T033 Ran the "scale/performance check": seeded 10,000 synthetic candidate rows directly via `$wpdb->insert()` in a loop. A 3-field filtered search (department + experience range + location) returned in **0.0048s**; a different 2-field combination (CTC range + notice period, 20 matches) in **0.0044s**; an unfiltered single-field search returning 1,251 matches in **0.0051s** — all far under the 2s target (SC-003). Synthetic rows fully deleted after.
- [X] T034 Verified the retention sweep: backdated one `active` and one `archived_rejected` record's `last_activity_at` to 25 months ago, ran `eminence_delete_expired_candidates()`, confirmed both deleted (2 of 2) while three non-expired records were untouched — retention is not exempting rejected records (FR-017).
- [X] T035 [P] Styled the Dashboard cards, duplicate-comparison callout, candidate profile view, and the public CV form in `assets/css/portal.css`. **Bug found and fixed**: the stylesheet was only enqueued on the Employee Login page template — the public form (on For Candidates, a different template) and the wp-admin screens (Dashboard etc.) were rendering unstyled. Fixed the front-end enqueue to also fire on any page whose content contains `[eminence_cv_submission]` (`has_shortcode()` check), and added a separate `admin_enqueue_scripts` hook gated on this plugin's own page slugs for the wp-admin screens. Confirmed visually via screenshot — both now match the site's design language.
- [X] T036 Ran quickstart.md Cleanup — all synthetic/test candidate records deleted (confirmed 0 rows remain) and all test employee accounts (`admin.test`, `recruiter.test`) removed; the one real account (`admin@eminenceconsultant.com`, created for the site owner in an earlier session) was left untouched.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Setup — BLOCKS all user stories
- **User Story 1 (Phase 3)**: Depends on Foundational only
- **User Story 2 (Phase 4)**: Depends on Foundational; reads data User Story 1 creates, so build after it for a meaningful independent test, though the code itself has no hard dependency on US1's files
- **User Story 3 (Phase 5)**: Depends on Foundational; its review step re-uses US1's duplicate-check and produces `active` records US2's search must find — build after both for the same reason as US2
- **User Story 4 (Phase 6)**: Depends on Foundational, and reads counts that only mean something once US1-US3 exist; also modifies US1 (011)'s login redirect and admin menu
- **Polish (Phase 7)**: Depends on all four user stories being complete

### Recommended order

Sequential P1 → P2 → P3 → P4, same reasoning as 011-employee-login: later stories read data
or attach UI to what earlier stories build, even though the underlying code dependencies are
looser than that.

### Parallel Opportunities

- T006, T007 (Foundational, after T005)
- T030, T031, T032, T035 (Polish, after everything else)

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational
3. Complete Phase 3: User Story 1
4. **STOP and VALIDATE**: quickstart.md Scenarios 1-5 — a working candidate database with
   duplicate detection, even before search exists
5. Demo: employees can start building the real candidate pipeline immediately

### Incremental Delivery

1. Setup + Foundational → schema and shared data layer ready
2. US1 → candidates can be added (MVP)
3. US2 → candidates can be found — the database starts paying off
4. US3 → the public pipeline opens up, gated by real review
5. US4 → the dashboard ties it all together, and Admin's login finally lands somewhere real
6. Polish → the security/scale verification sweep, run once everything exists

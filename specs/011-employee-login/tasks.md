# Tasks: Employee Login & Authentication

**Input**: Design documents from `/specs/011-employee-login/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/portal-auth-contract.md, quickstart.md

**Tests**: No automated test framework applies (see plan.md Testing) — this feature has real
security-critical logic, so verification is the scripted WP-CLI/curl checks in quickstart.md,
tracked as explicit tasks below rather than a separate `tests/` phase.

**Organization**: Tasks are grouped by the four user stories in spec.md (US1-US4, priority
order P1-P4). All paths are relative to the repository root.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: US1 = Employee signs in; US2 = Session ends after inactivity; US3 = Admin
  provisions/removes accounts; US4 = Employee signs out

---

## Phase 1: Setup

**Purpose**: Plugin scaffold

- [X] T001 Create the plugin bootstrap file with WordPress's required plugin header comment (name, description, version) at `wp-content/plugins/eminence-portal/eminence-portal.php`
- [X] T002 [P] Create the empty `includes/` and `assets/css/` directories per plan.md's Project Structure
- [X] T003 [P] Create `wp-content/plugins/eminence-portal/assets/css/portal.css` (empty stub — login form and account-management styles land here in later tasks)

**Checkpoint**: Plugin is installable and activatable in `wp-admin → Plugins`, does nothing yet.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Roles, capabilities, and the one theme integration point every user story needs

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T004 Create `wp-content/plugins/eminence-portal/includes/roles.php`: on `register_activation_hook`, add the two custom roles from data-model.md — `eminence_recruiter` (capability: `read`) and `eminence_portal_admin` (capabilities: `read`, `eminence_manage_employees`) (research.md §2)
- [X] T005 [P] In `includes/roles.php`, add `register_deactivation_hook` that leaves roles and user accounts untouched (deactivating the plugin must not lock anyone out or delete data — only an explicit uninstall would remove the roles, which is out of scope for this feature)
- [X] T006 [P] Create `wp-content/plugins/eminence-portal/includes/capabilities.php`: constants for the role slugs and the `eminence_manage_employees` capability, plus a small `eminence_portal_current_user_role_label()` helper (Admin/Recruiter display string) reused by the landing area
- [X] T007 Wire `eminence-portal.php` to `require` every file under `includes/` and hook `roles.php`'s activation/deactivation functions (depends on T001, T004, T005, T006)
- [X] T008 Update `wp-content/themes/eminence-consultant/page-employee-login.php` (from 001-site-shell-navigation) to render `do_shortcode('[eminence_employee_login]')` in place of its current static "coming soon" copy — the single theme↔plugin integration point (contracts/portal-auth-contract.md)

**Checkpoint**: Roles exist, plugin is wired, the login page is ready to hand off to the (still-empty) shortcode.

---

## Phase 3: User Story 1 - Employee signs in (Priority: P1) 🎯 MVP

**Goal**: A real login form replaces the placeholder; valid credentials sign an employee in
and land them on a page confirming their name and role; invalid credentials, unknown
accounts, deactivated accounts, and locked-out accounts all get the same generic refusal.

**Independent Test**: Manually create one WP-CLI user per quickstart.md Setup step 2, then
run quickstart.md Scenarios 1-4 — deliverable and demoable without US2/US3/US4 existing.

### Implementation for User Story 1

- [X] T009 [US1] Create `wp-content/plugins/eminence-portal/includes/auth.php`: render the login form markup (identifier + password fields, a nonce field, and a slot for an error/status message) when nobody is logged in
- [X] T010 [US1] In `includes/auth.php`, handle login form submission: verify the nonce, call `wp_signon()`, and on failure show exactly one generic error message regardless of cause (research.md §7)
- [X] T011 [US1] In `includes/auth.php`, add an `authenticate` filter check: if the account's `eminence_account_status` user meta is `deactivated` (default to `active` when the meta doesn't exist yet, e.g. accounts created directly via WP-CLI in testing), fail authentication with the same generic error from T010 (research.md §5, FR-004 spirit)
- [X] T012 [US1] In `includes/auth.php`, add failed-login lockout: a transient counted per account, 5 failures within 15 minutes locks that account out for 15 minutes; a locked-out attempt (even with the correct password) shows the same generic error (research.md §4)
- [X] T013 [US1] Create `wp-content/plugins/eminence-portal/includes/shortcodes.php`: register `[eminence_employee_login]`, calling `auth.php`'s login-form renderer when logged out (depends on T009-T012)
- [X] T014 [US1] In `includes/shortcodes.php`, add the logged-in branch: when `is_user_logged_in()`, render the authenticated landing area showing display name and role label (via `capabilities.php`'s helper from T006) instead of the login form — this is what makes AS3 (no authenticated content without a session) true, since the same URL only ever renders one branch or the other (depends on T006, T013)
- [X] T015 [US1] In `includes/auth.php`, log successful and failed sign-in attempts (account identifier + outcome + timestamp, no passwords) via `error_log()` or a small custom log table decision left to implementation — satisfies FR-013

**Checkpoint**: User Story 1 fully functional and independently testable — quickstart.md Scenarios 1-4 pass.

---

## Phase 4: User Story 2 - Session ends after inactivity (Priority: P2)

**Goal**: A session left idle for 30 minutes ends itself on the next request, with a clear
message; active use never gets cut off; a deactivated account's live session is also cut.

**Independent Test**: Log in (US1), simulate idle time per quickstart.md Scenario 5, confirm
forced sign-out — independently testable once US1 exists, without US3/US4.

### Implementation for User Story 2

- [X] T016 [US2] Create `wp-content/plugins/eminence-portal/includes/session-timeout.php`: single `template_redirect` function (consolidated from the originally-planned separate `init` update + `template_redirect` check into one function, since the check needs the *previous* activity value before refreshing it — splitting across two hooks added no value) that reads `eminence_last_activity` and refreshes it to the current timestamp for any logged-in employee
- [X] T017 [US2] In the same function, if `time() - eminence_last_activity > 1800`, call `wp_logout()` and redirect to the login URL with a `?eminence_notice=timeout` query flag (research.md §3). **Bug found and fixed during quickstart validation**: the initial check used `if ( $last_activity && ... )`, which treats a stored value of exactly `0` as falsy/"unset" and silently skips the check — that made the quickstart's own idle-simulation method (`wp user meta update ... eminence_last_activity 0`) pass without actually testing anything. Fixed to check `'' !== $last_activity_raw` explicitly (only truly-unset meta is skipped), confirmed by re-running Scenario 5 and observing the `timeout` redirect actually fire.
- [X] T018 [US2] In the same check, also verify `eminence_account_status === 'active'`; if not, `wp_logout()` and redirect with `?eminence_notice=deactivated` — covers the "deactivated mid-session" edge case (research.md §5)
- [X] T019 [US2] Wire `eminence-portal.php` to require and hook `session-timeout.php` (depends on T007, T016-T018)
- [X] T020 [US2] In `includes/auth.php`'s `eminence_portal_notice_message()`, map the `eminence_notice` query flag to the matching message ("Your session expired..." / "This account is no longer available."), read and rendered by `eminence_portal_render_login_form()` (depends on T013, T017, T018)

**Checkpoint**: User Stories 1 AND 2 both work independently — quickstart.md Scenarios 5-6 pass.

---

## Phase 5: User Story 3 - Admin provisions and removes employee accounts (Priority: P3)

**Goal**: A Portal Admin can create, deactivate, and reactivate employee accounts from a
`wp-admin` screen no Recruiter can see or reach; the last active Admin can never be removed.

**Independent Test**: Manually create one bootstrap Admin per quickstart.md Setup step 2,
log in as them, run quickstart.md Scenarios 7-10 — independently testable once US1 exists.

### Implementation for User Story 3

- [X] T021 [US3] Create `wp-content/plugins/eminence-portal/includes/account-management.php`: register a `wp-admin` submenu page (`add_menu_page`) gated on the `eminence_manage_employees` capability (research.md §6) — WordPress itself hides the menu item and denies direct access (confirmed via curl: a logged-in Recruiter gets HTTP 403 from WordPress core's own capability check, satisfying FR-009/AS3 by construction, before this plugin's own code ever runs)
- [X] T022 [US3] In `includes/account-management.php`, render the account list: every WP user holding either `eminence_recruiter` or `eminence_portal_admin`, showing name, identifier, role, and `eminence_account_status`
- [X] T023 [US3] In `includes/account-management.php`, add the "create account" form (name, login identifier/email, role, initial password) and handler: verify the nonce and capability, `wp_insert_user()` with the chosen role, set `eminence_account_status` to `active` (depends on T004, T022)
- [X] T024 [US3] In `includes/account-management.php`, add a per-row "deactivate"/"reactivate" action toggling `eminence_account_status`, nonce- and capability-checked (depends on T022)
- [X] T025 [US3] In the deactivate/demote handler from T024, add the last-Admin guard: count active `eminence_portal_admin` accounts before allowing the action; if it would drop to zero, block it and show an explanatory message instead (FR-010, depends on T024)
- [X] T026 [US3] Wire `eminence-portal.php` to require and hook `account-management.php` (depends on T007, T021)

**Checkpoint**: User Stories 1-3 all work independently — quickstart.md Scenarios 7-10 pass.

---

## Phase 6: User Story 4 - Employee signs out (Priority: P4)

**Goal**: A logged-in employee can end their session immediately rather than waiting for the timeout.

**Independent Test**: Log in (US1), use the sign-out control, confirm the landing area is no longer reachable without logging in again — quickstart.md Scenario 11.

### Implementation for User Story 4

- [X] T027 [US4] In `includes/shortcodes.php`'s logged-in branch (T014), add a sign-out control (nonce-protected button) alongside the name/role confirmation
- [X] T028 [US4] In `includes/auth.php`, handle the sign-out action: verify the nonce, call `wp_logout()`, redirect to the login URL with a `?eminence_notice=signed_out` confirmation flag (depends on T027)

**Checkpoint**: All four user stories independently functional — quickstart.md Scenario 11 passes.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Verification sweep across all four stories

- [X] T029 Run every scenario in quickstart.md end-to-end against a real local WordPress install (junctioned `eminence-portal` into the preview's `wp-content/plugins/`, activated it via WP-CLI). All 11 scenarios pass — see the T017 bug note above for the one issue found and fixed along the way. (Two environment artifacts hit during testing, unrelated to this feature's code: a stray `.maintenance` file from the preview's own WP-Cron auto-update activity caused a transient 503, and the plugin's first symlink was accidentally created as a plain directory copy instead of a live link, so edits weren't reaching the preview until replaced with a proper NTFS junction — both resolved, neither touched anything under version control.)
- [X] T030 [P] Run the quickstart.md "Password storage check" — confirmed via direct PHP (`wp db query` isn't usable in this SQLite-backed preview) that `user_pass` is a `$wp$2y$10$...` bcrypt hash, never plaintext, for every account created during testing
- [X] T031 [P] Run the quickstart.md "Unauthenticated redirect sweep" and "Role-gate sweep" via curl: zero cookies → login form only, never landing content; logged in as Recruiter → HTTP 403 from the account-management URL
- [X] T032 Nonce/CSRF review: confirmed every state-changing form (login T010, sign-out T028, create/deactivate/reactivate T023-T024) verifies its nonce before acting — exercised directly during T029's scripted validation, not just read-through
- [X] T033 [P] Style the login form and authenticated landing area in `assets/css/portal.css`, enqueued only on the Employee Login page template; the `wp-admin` account-management screen intentionally uses WordPress's own native admin styles (`widefat`, `form-table`, `button`) rather than custom CSS, consistent with research.md §6's reasoning for building it in wp-admin in the first place
- [X] T034 Ran quickstart.md Cleanup — all test accounts (`admin.test`, `recruiter.test`, `jane.recruiter@example.com`) deleted; confirmed `wp user list --role__in=eminence_recruiter,eminence_portal_admin` returns empty

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Setup — BLOCKS all user stories
- **User Story 1 (Phase 3)**: Depends on Foundational only
- **User Story 2 (Phase 4)**: Depends on Foundational; extends US1's shortcode (T013) and reuses its account-status check (T011) — build after US1
- **User Story 3 (Phase 5)**: Depends on Foundational; independent of US2, but account creation (T023) is what makes US1 testable with more than one manually-created account
- **User Story 4 (Phase 6)**: Depends on US1's logged-in branch (T014) existing to attach a sign-out control to
- **Polish (Phase 7)**: Depends on all four user stories being complete

### Recommended order

Given the dependencies above (US2 extends US1's shortcode; US4 attaches to US1's logged-in
branch), build in priority order P1 → P2 → P3 → P4 rather than in parallel, even though US3
has no hard code dependency on US2.

### Parallel Opportunities

- T002, T003 (Setup)
- T005, T006 (Foundational, after T004)
- T030, T031, T033 (Polish, after everything else)

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational
3. Complete Phase 3: User Story 1
4. **STOP and VALIDATE**: quickstart.md Scenarios 1-4, using one manually-created WP-CLI
   account (Setup step 2) since account management (US3) doesn't exist yet
5. Demo: a real, working login gate replacing the placeholder — the core of "Module 2"

### Incremental Delivery

1. Setup + Foundational → login page is wired but empty
2. US1 → real login/landing area (MVP)
3. US2 → sessions actually expire (closes the Security Baseline gap immediately after MVP)
4. US3 → Admins can self-serve account creation instead of needing WP-CLI
5. US4 → explicit sign-out, smallest remaining piece
6. Polish → the security-critical verification sweep, run once everything exists

# Tasks: Site Shell & Global Navigation

**Input**: Design documents from `/specs/001-site-shell-navigation/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/theme-shell-contract.md, quickstart.md

**Tests**: No automated test framework applies to this feature (see plan.md Testing) — this
feature is a WordPress theme. Verification is manual QA + Lighthouse/axe-core, tracked as
explicit tasks below rather than as a separate `tests/` phase.

**Organization**: Tasks are grouped by the two P1 user stories in spec.md.

**Implementation status (2026-07-26)**: All pure-code tasks are implemented in
`wp-content/themes/eminence-consultant/`. Tasks requiring a live WordPress/MySQL install,
`wp-admin` access, a browser, or a plugin directory (T004, T005, T027, T031, T032, T034–T038)
could not be executed in this environment — no PHP/WordPress runtime is available here. They
remain unchecked below with a note on what's needed to complete them.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: US1 = Visitor Navigates the Site Consistently; US2 = Business Owner Updates
  Content Without a Developer
- All paths are relative to `wp-content/themes/eminence-consultant/` unless stated otherwise

---

## Phase 1: Setup

**Purpose**: Theme scaffold and environment prerequisites

- [x] T001 Create theme directory and `style.css` header block (theme name, author, version — WordPress's required theme metadata comment) at `style.css`
- [x] T002 Create `functions.php` with theme setup: `add_theme_support('title-tag')`, `add_theme_support('html5', [...])`, `register_nav_menus(['primary' => 'Primary Navigation', 'footer' => 'Footer Navigation'])`
- [x] T003 [P] Add a placeholder `screenshot.png` (WordPress admin theme-list requirement) — 1x1 stub only; replace with a real screenshot before launch
- [ ] T004 [P] **BLOCKED — needs wp-admin**: Install and activate the SEO plugin chosen in research.md #1; disable its XML sitemap module; confirm WordPress core's native sitemap is reachable at `/wp-sitemap.xml`
- [ ] T005 **BLOCKED — needs wp-admin**: Create the 9 WordPress Pages (Home, About Us, What We Do, For Employers, For Candidates, Testimonials, Industry Leaders We've Met, Contact Us, Privacy Policy) plus the Employee Login page, each with placeholder body text; set Home as the static front page in Settings → Reading; assign the "Employee Login (Placeholder)" template to the Employee Login page

**Checkpoint**: Theme is installable and activatable; content shell exists to render into.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Shared theme infrastructure both user stories build on

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T006 Create base `header.php`: doctype, `<head>` with `wp_head()`, `body_class()`, opening page-wrapper markup (no nav content yet)
- [x] T007 Create base `footer.php`: closing page-wrapper markup, `wp_footer()` (no footer content yet)
- [x] T008 [P] Create `template-parts/navigation.php`, included from `header.php` — built with full markup directly rather than a stub-then-fill pass (merged with T015)
- [x] T009 [P] Create `template-parts/footer-widgets.php`, included from `footer.php` — built with full markup directly rather than a stub-then-fill pass (merged with T019)
- [x] T010 Create `page.php`, the generic content template rendering `the_content()` — this is what satisfies FR-014 CMS editability for every content page (depends on T006, T007)
- [x] T011 [P] Register Customizer Social Link fields in `functions.php` via `customize_register` (LinkedIn, Facebook, Instagram, Twitter/X — per data-model.md)
- [x] T012 [P] Create `assets/css/theme.css`: CSS reset, mobile-first typography, layout grid, breakpoint variables
- [x] T013 Enqueue `theme.css` via `wp_enqueue_scripts` in `functions.php` (depends on T012)
- [x] T014 [P] Define the GA4 measurement ID as a theme constant/option in `functions.php` (config only — no script output yet, that's US1)

**Checkpoint**: Foundation ready — both user stories can now be implemented.

---

## Phase 3: User Story 1 - Visitor Navigates the Site Consistently on Any Device (Priority: P1) 🎯 MVP

**Goal**: Consistent header/footer nav, mobile menu, 404 page, Employee Login placeholder, and
gated GA4 + cookie consent — everything spec.md Acceptance Scenarios 1–7 require.

**Independent Test**: Load any page and confirm header/footer/mobile-menu/404/Employee-Login/
consent-gated-analytics all behave per quickstart.md scenarios 1–9 — independent of US2 (content
editability) being finished, since placeholder content is enough to test navigation itself.

### Implementation for User Story 1

- [x] T015 [P] [US1] Build full primary nav markup in `template-parts/navigation.php`: links to all 8 content pages + Employee Login, using `wp_nav_menu(['theme_location' => 'primary'])`
- [x] T016 [P] [US1] Build `assets/js/mobile-nav.js`: hamburger toggle, closes on link click, no body-scroll lock issues
- [x] T017 [US1] Add mobile nav CSS (collapsed/expanded states at the mobile breakpoint) to `assets/css/theme.css` (depends on T015)
- [x] T018 [US1] Wire `navigation.php` into `header.php` and enqueue `mobile-nav.js` (depends on T015, T016)
- [x] T019 [P] [US1] Build footer nav + social icons in `template-parts/footer-widgets.php`: `wp_nav_menu(['theme_location' => 'footer'])`, social icons reading the Customizer fields from T011 (omit any with an empty URL), a link to the Privacy Policy page (depends on T011)
- [x] T020 [US1] Wire `footer-widgets.php` into `footer.php` (depends on T019)
- [x] T021 [P] [US1] Create `404.php`: styled "page not found" message with a link back to Home
- [x] T022 [P] [US1] Create `page-employee-login.php`: static "coming soon" placeholder — no form, no login handler, no session logic (constitution Principle VIII)
- [x] T023 [P] [US1] Build `template-parts/cookie-notice.php`: accept/decline consent banner markup, linking to the Privacy Policy page
- [x] T024 [P] [US1] Build `assets/js/consent.js`: read/write the `eminence_consent` first-party cookie; expose a function reporting current consent state
- [x] T025 [US1] Add gated GA4 `gtag.js` injection to `footer.php`: only output the tag when `consent.js` reports "accepted" (depends on T014, T023, T024)
- [x] T026 [US1] Wire `cookie-notice.php` into `footer.php` and enqueue `consent.js` (depends on T023, T024)
- [ ] T027 [US1] **BLOCKED — needs a running WordPress site + browser**: Manual QA: execute quickstart.md scenarios 1–9 against a local WordPress install; fix any failures (depends on T015–T026)

**Checkpoint**: User Story 1 complete and independently testable — this is the MVP.

---

## Phase 4: User Story 2 - Business Owner Updates Site Content Without a Developer (Priority: P1)

**Goal**: Every content page's text and images are editable in `wp-admin` with no code
deployment, and alt text is settable at upload time — spec.md Acceptance Scenarios covering
CMS editability.

**Independent Test**: Log into `wp-admin`, edit text and swap an image (with alt text) on any
page, publish, confirm the change is live per quickstart.md scenarios 10–11 — independent of
US1's navigation work being finished, since this only exercises the editor and `page.php`.

### Implementation for User Story 2

- [x] T028 [P] [US2] Add `add_theme_support('post-thumbnails')` and register at least one custom image size for content images in `functions.php`
- [x] T029 [P] [US2] Add `add_theme_support('editor-styles')` + `add_editor_style()` so the block-editor preview matches front-end styling
- [x] T030 [US2] Verify `page.php` renders 100% of visible text/images via `the_content()`/featured image with zero hardcoded copy (depends on T010, T028) — confirmed by inspection: no hardcoded body copy exists in page.php
- [ ] T031 [US2] **BLOCKED — depends on T004**: Confirm the SEO plugin's per-page title/description fields render into `<head>` via `header.php` on every Page (the `wp_head()` hook point that will receive them is in place)
- [ ] T032 [US2] **BLOCKED — needs a running WordPress site + browser**: Manual QA: execute quickstart.md scenarios 10–11 (owner edits text and an image including alt text; change appears live) (depends on T030, T031)

**Checkpoint**: Both P1 user stories complete and independently functional.

---

## Phase 5: Polish & Cross-Cutting Concerns

**Purpose**: Site-wide validation against spec.md Success Criteria

- [x] T033 [P] Create a minimal `front-page.php` (`get_header()` / `the_content()` / `get_footer()`) as Home's shell wrapper — hero-specific markup is deferred to the `002-home-page` implementation; this only ensures Home renders inside the shell without erroring
- [ ] T034 [P] **BLOCKED — needs a running WordPress site + browser**: Run Lighthouse (Performance/Accessibility/SEO) against Home and one content page per quickstart.md; remediate any failures against the SC-004 <3s target
- [ ] T035 [P] **BLOCKED — needs a running WordPress site + browser**: Run an axe-core scan against the same two pages per quickstart.md; remediate any critical/serious violations
- [ ] T036 **BLOCKED — needs a running WordPress site**: Run a broken-link check across the primary and footer navigation per quickstart.md
- [ ] T037 **BLOCKED — needs a running WordPress site**: Verify `/wp-sitemap.xml` lists all 9 published pages
- [ ] T038 **BLOCKED — depends on T005, T027, T032, T034–T037**: Final sign-off: confirm every quickstart.md "Done when" item passes; update spec.md Status from Draft to Implemented

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — start immediately
- **Foundational (Phase 2)**: Depends on Setup — BLOCKS both user stories
- **User Story 1 (Phase 3)**: Depends on Foundational only — no dependency on US2
- **User Story 2 (Phase 4)**: Depends on Foundational only — no dependency on US1
- **Polish (Phase 5)**: Depends on both user stories being complete

### User Story Dependencies

- US1 and US2 are independent of each other (both depend only on Phase 2) — they can be built
  in either order, or in parallel by two developers, matching their equal P1 priority.

### Within Each User Story

- Navigation/footer markup before wiring into header.php/footer.php
- Consent banner + script before gated GA4 injection
- Implementation before its manual-QA task

### Parallel Opportunities

- T003, T004 (Setup) can run in parallel
- T008, T009, T011, T012, T014 (Foundational) can run in parallel — different files
- T015, T016, T019, T021, T022, T023, T024 (US1) can run in parallel — different files
- T028, T029 (US2) can run in parallel — same file (`functions.php`) but non-overlapping calls; treat as parallel-safe only if merged carefully
- Once Phase 2 is done, all of Phase 3 (US1) and Phase 4 (US2) can proceed in parallel

---

## Parallel Example: Phase 2 (Foundational)

```bash
Task: "Create empty template-parts/navigation.php stub"
Task: "Create empty template-parts/footer-widgets.php stub"
Task: "Register Customizer Social Link fields in functions.php"
Task: "Create assets/css/theme.css with mobile-first base styles"
Task: "Define GA4 measurement ID as a theme constant/option in functions.php"
```

## Parallel Example: Phase 3 (User Story 1)

```bash
Task: "Build primary nav markup in template-parts/navigation.php"
Task: "Build assets/js/mobile-nav.js hamburger toggle"
Task: "Build footer nav + social icons in template-parts/footer-widgets.php"
Task: "Create 404.php styled template"
Task: "Create page-employee-login.php placeholder"
Task: "Build template-parts/cookie-notice.php consent banner markup"
Task: "Build assets/js/consent.js consent state handling"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (blocks everything)
3. Complete Phase 3: User Story 1
4. **STOP and VALIDATE**: run quickstart.md scenarios 1–9
5. This alone proves every downstream page spec (002–010) has somewhere real to render into

### Incremental Delivery

1. Setup + Foundational → shell installable
2. User Story 1 → navigation/404/consent/Employee-Login all work → validate → this is the MVP
3. User Story 2 → content editability confirmed → validate
4. Polish → Lighthouse/axe/sitemap/broken-link gates → sign off, mark spec.md Implemented

## Notes

- [P] tasks touch different files and have no unmet dependencies
- Both user stories are P1 — priority doesn't imply sequencing here, only that neither is
  deferrable; US1 is listed first because navigation must exist before anyone can reach a
  page to edit it, which is a practical (not priority) ordering.
- Commit after each checkpoint (end of Phase 1, 2, 3, 4, 5), not after every single task
- This feature has no candidate data, so constitution Principles II–IV, VI are not exercised
  by any task here (per plan.md Constitution Check) — no task list entry needed for them

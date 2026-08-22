# Implementation Plan: Candidate Database, CV Upload & Review Workflow

**Branch**: `012-candidate-database` | **Date**: 2026-08-21 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/012-candidate-database/spec.md`

## Summary

Extend the `eminence-portal` plugin (011-employee-login) with a real candidate
database: a custom indexed table (not postmeta/CPT — see research.md #1) for
the 20 BRD-6.2 fields, CV file upload stored outside any publicly-reachable
path, duplicate detection on phone/email at both entry points, a
search/filter screen with CSV export, a public 5-field submission form that
lands in a Pending Review queue, an Approve/Reject workflow, and a real
Dashboard (employee count, active-CV count, pending-review count, recent
logins) that a Portal Admin now lands on immediately after signing in.

## Technical Context

**Language/Version**: PHP 8.1+ (matches the theme and the existing
`eminence-portal` plugin).

**Primary Dependencies**: WordPress core + the existing `eminence-portal`
plugin only — no new external dependency, no Composer packages. CSV export
uses PHP's native `fputcsv()` rather than a spreadsheet library
(research.md #4); CV upload validation reuses WordPress core's
`wp_handle_upload()` with a custom upload directory and MIME/size checks
layered on top.

**Storage**: MySQL (SQLite locally, same dev-only substitution as the rest of
this project) via one new custom table, `{$wpdb->prefix}eminence_candidates`,
created with `dbDelta()` on plugin activation — not a custom post type with
postmeta. Every one of the 20 BRD-6.2 fields is a real, indexed column
(research.md #1) so the combined range/equality filters in FR-005 can hit the
SC-003 <2s target at 10,000 rows. A single table with a `status` column
(`active` / `pending_review` / `archived_rejected`) holds all three states —
Approve/Reject are `UPDATE`s, not cross-table moves (research.md #2).

**Testing**: Same approach as 011-employee-login — no unit-test framework;
verified via a scripted WP-CLI + curl quickstart.md pass covering every
acceptance scenario, plus a synthetic-data seed step (thousands of rows) to
actually measure the <2s search target at scale rather than assume it.

**Target Platform**: Web, responsive. Same browser support as the rest of
the site.

**Project Type**: Web (WordPress plugin extension + one content placement on
an existing page).

**Performance Goals**: Filtered search returns in under 2 seconds at up to
10,000 candidate profiles (BRD Section 8, spec SC-003). Every other page
stays under the site's existing 3-second bar (constitution Principle VII).

**Constraints**: CV files MUST NEVER be reachable by an unauthenticated
request (FR-015, Principle II) — served only through a capability-gated
download endpoint, never a direct uploads-folder URL (research.md #3).
Duplicate detection MUST run at both entry points before a record is
finalized (FR-002, Principle IV, NON-NEGOTIABLE). A public submission MUST
NOT be writable into the searchable (`active`) state directly (FR-009,
Principle III, NON-NEGOTIABLE). The 24-month retention rule applies to every
status, including `archived_rejected` (FR-017, Principle VI). No new
external service or dependency (Principle I) — CSV over a spreadsheet
library, local/server file storage over cloud storage (research.md #3, #4).

**Scale/Scope**: Up to 10,000 candidate profiles (BRD Section 8). CV files
up to 5MB each, PDF/DOC/DOCX only. Extends the existing two-role model
(Recruiter, Admin) from 011-employee-login — no new roles.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| # | Principle | Applies? | Assessment |
|---|-----------|----------|------------|
| I | Fixed Technology Stack | Yes | PASS. Stays inside `eminence-portal` (WP core + custom plugin + MySQL). CSV export and server-local file storage were chosen specifically to avoid adding any new library or external service (research.md #3, #4). |
| II | Role-Gated Access to Candidate Data | Yes | PASS, and central. Every candidate-data screen and the CV download endpoint require `eminence_manage_candidates`; no candidate PII is ever rendered on an unauthenticated page or served from a guessable/public file URL. |
| III | Public/Internal Data Separation | Yes | PASS, and central. FR-009 makes this the load-bearing rule of the whole feature: a public submission is stored with `status = pending_review` and is structurally excluded from every search query (which always filters `status = active`) until an explicit Approve. |
| IV | Mandatory Duplicate Detection | Yes | PASS, and central (NON-NEGOTIABLE). One shared duplicate-check function is called from both the direct-add path (User Story 1) and the review-approval path (User Story 3) — see research.md #5 — so there is exactly one place this logic can drift, not two. |
| V | Security Baseline | Yes | PASS. Reuses 011-employee-login's authentication/session/role machinery entirely; adds no new login surface. CV downloads go through the same capability check as every other portal screen. |
| VI | Candidate Data Retention | Yes | PASS. FR-017 explicitly applies the existing 24-month-from-last-activity rule (established in the constitution and published in 010-privacy-policy-page) to `archived_rejected` records too, not just `active` ones. |
| VII | Performance and Scale Targets | Yes | PASS, with an explicit approach: real indexed columns (not postmeta EAV) plus pagination is what makes the <2s / 10,000-row target achievable, and quickstart.md's synthetic-data seed step measures it rather than assumes it. |
| VIII | Phase Discipline — No Scope Creep | Yes | PASS. This feature touches 011-employee-login's code in exactly two small, justified spots (admin menu restructured to add Dashboard/Candidates/Pending Review items; Admin's post-login redirect now targets the real Dashboard instead of the Employee Accounts screen) — both are the natural continuation of "admin shall land on his dashboard," not scope drift into 011's own domain (login/sessions/accounts are untouched). |

**Gate result**: PASS. No violations to justify — Complexity Tracking table is empty.

**Post-design re-check** (after Phase 1 — data-model.md, contracts/,
quickstart.md): No new entities, dependencies, or integrations were
introduced during design beyond what Technical Context already declared (one
new table, the existing plugin, no external services). Gate result
unchanged: **PASS**.

## Project Structure

### Documentation (this feature)

```text
specs/012-candidate-database/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output
│   └── candidate-data-contract.md
└── tasks.md              # Phase 2 output (/speckit-tasks — not created by this command)
```

### Source Code (repository root)

Extends the existing `eminence-portal` plugin (011-employee-login) with new
`includes/` files; touches exactly one existing plugin file per the
Constitution Check row above, plus one existing theme page's content (not
its template code).

```text
wp-content/
├── plugins/
│   └── eminence-portal/
│       ├── eminence-portal.php            # MODIFIED: require the new includes below
│       ├── includes/
│       │   ├── capabilities.php           # MODIFIED: add eminence_manage_candidates
│       │   │                              # and eminence_edit_any_candidate constants
│       │   ├── auth.php                   # MODIFIED: Admin post-login redirect now
│       │   │                              # targets the Dashboard page (below), not
│       │   │                              # Employee Accounts directly
│       │   ├── account-management.php     # MODIFIED: admin_menu registration becomes
│       │   │                              # a menu group (Dashboard default, Employee
│       │   │                              # Accounts, Candidates, Add Candidate,
│       │   │                              # Pending Review as submenu items)
│       │   ├── candidates-schema.php      # NEW: dbDelta table creation, hooked onto
│       │   │                              # the same activation hook roles.php uses
│       │   ├── candidate-repository.php   # NEW: all $wpdb reads/writes — insert,
│       │   │                              # duplicate lookup, filtered search/count,
│       │   │                              # approve/reject, counts for the dashboard.
│       │   │                              # The one place SQL for this feature lives.
│       │   ├── cv-storage.php             # NEW: upload validation (type/size),
│       │   │                              # non-public storage path, and the
│       │   │                              # capability-gated download endpoint
│       │   ├── candidate-form.php         # NEW: internal add-candidate screen
│       │   │                              # (wp-admin) + duplicate-check UI
│       │   ├── candidate-search.php       # NEW: search/filter screen, results table,
│       │   │                              # CSV export (wp-admin)
│       │   ├── candidate-review.php       # NEW: Pending Review queue screen +
│       │   │                              # Approve/Reject handlers (wp-admin)
│       │   ├── public-cv-form.php         # NEW: [eminence_cv_submission] shortcode —
│       │   │                              # the public 5-field form + its handler
│       │   └── dashboard.php              # NEW: the Dashboard screen (wp-admin) —
│       │                                  # 4 cards, reads counts from
│       │                                  # candidate-repository.php and 011's
│       │                                  # existing account/sign-in-log data
│       └── assets/css/portal.css          # MODIFIED: styles for the new screens
└── themes/
    └── eminence-consultant/
        # No template code changes. The For Candidates page (006) gets
        # [eminence_cv_submission] inserted into its existing WP-editor content —
        # a content edit (scripted via WP-CLI in tasks.md), same as any other
        # business-owner page edit, not a template/code change.
```

**Structure Decision**: Everything new lives inside the existing
`eminence-portal` plugin, continuing 011's Principle-I-driven separation from
the theme. One new custom table instead of a CPT (research.md #1) is the one
structural deviation from "use WordPress's native content types" this
project has otherwise followed — justified specifically by the <2s/10,000-row
search requirement, which postmeta-based `meta_query` filtering does not
reliably hit at that scale with this many simultaneously-filterable fields.

## Complexity Tracking

*No Constitution Check violations — this table is intentionally empty.*

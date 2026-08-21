# Implementation Plan: Employee Login & Authentication

**Branch**: `011-employee-login` | **Date**: 2026-08-19 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/011-employee-login/spec.md`

## Summary

Replace the placeholder Employee Login page (built as static content in
001-site-shell-navigation) with real authentication: a login form, 30-minute
inactivity session timeout, sign-out, and Admin-only employee account
provisioning/removal — all built as the custom PHP plugin the constitution
requires for portal features (Principle I), not theme code. No candidate data
of any kind is touched by this feature; it exists purely to establish the
authenticated, role-gated foundation that a later candidate-database feature
will sit behind.

## Technical Context

**Language/Version**: PHP 8.1+ (WordPress 6.x compatible) — matches the theme.

**Primary Dependencies**: WordPress core (self-hosted) only. A new custom
plugin, `eminence-portal`, per constitution Principle I. Authentication,
password hashing, and login cookies reuse WordPress core's own user system
(`wp_signon()`, `wp_set_auth_cookie()`, `wp_hash_password()`) rather than a
hand-rolled credential store — WP core's hashing is already audited,
maintained, and exactly what "no alternative framework" (Principle I) points
toward. Two new custom roles (`eminence_recruiter`, `eminence_portal_admin`)
are registered by the plugin with their own minimal capability sets — kept
distinct from WordPress's own built-in `administrator` role, which stays
reserved for actual site/WP-admin management and is not equivalent to the
BRD's "Admin" business role (a Portal Admin who can create employee accounts
has no business being able to install plugins or edit theme files). No
consent-management-style third-party plugin, matching the project's existing
bias (see 001-site-shell-navigation/research.md) toward small hand-rolled
code over bundled plugins for narrowly-scoped needs.

**Storage**: MySQL via WordPress's own `wp_users`/`wp_usermeta` tables — no
new custom database tables for this feature. Employee accounts are WP users
carrying one of the two new roles; active/deactivated status and
last-activity-for-timeout tracking are stored as user meta. This is the
correct scope boundary per the spec's Assumptions: the candidate database
(BRD Section 6.2's 20 fields) is explicitly out of scope and will need its
own custom tables in a later feature, not this one.

**Testing**: No unit-test framework was introduced in prior features
(presentation-layer WordPress theme work), but this feature has real,
security-critical business logic — that changes the calculus. Validated via:
(a) a manual QA checklist walking every acceptance scenario in spec.md, (b) a
scripted WP-CLI + curl verification pass in quickstart.md covering the
NON-NEGOTIABLE paths specifically — unauthenticated redirect, session
timeout, and role-gating on account management — so those aren't verified by
eyeballing alone, consistent with how this project has verified prior
features (curl smoke tests, headless-browser screenshots).

**Target Platform**: Web, responsive. Same browser support as the rest of the
site (constitution Technical Constraints).

**Project Type**: Web (WordPress custom plugin + a thin theme integration
point).

**Performance Goals**: Login and the authenticated landing page load under 3
seconds (constitution Principle VII), same bar as every public page.

**Constraints**: HTTPS everywhere (inherited from hosting, same as
001-site-shell-navigation). Passwords hashed and salted via WordPress core's
own hashing, never stored or logged in plaintext (Principle V, FR-003).
Sessions end after 30 minutes of inactivity (Principle V, FR-006). Only
Portal Admin accounts can create or remove employee accounts (Principle V,
FR-009). No direct public access to the database layer — all reads/writes go
through WordPress core APIs and this plugin's own gated code, never raw
unauthenticated SQL (Principle V).

**Scale/Scope**: A handful of employee accounts (spec Assumptions) across
exactly two roles. One public login page (reusing 001's existing URL/nav
entry), one minimal authenticated landing area, and an Admin-only account
list/create/deactivate screen. No candidate data, search, or CV handling.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| # | Principle | Applies? | Assessment |
|---|-----------|----------|------------|
| I | Fixed Technology Stack | Yes | PASS. Built as `wp-content/plugins/eminence-portal/`, the custom PHP plugin the constitution names for portal features — not theme code, not a third-party auth plugin, not a different stack. |
| II | Role-Gated Access to Candidate Data | Foundational | PASS. This feature carries no candidate data (spec explicitly excludes it) but it is what Principle II's "authenticated, role-based access" will be built on top of — the two roles and the gating helpers this plan defines are exactly the mechanism a later candidate-database feature must reuse, not reinvent. |
| III | Public/Internal Data Separation | No | N/A — no candidate submissions or review queue exist in this feature. |
| IV | Mandatory Duplicate Detection | No | N/A — no candidate records are created by this feature. |
| V | Security Baseline | Yes | PASS, and central to this feature. HTTPS (inherited). Hashed/salted credentials via WP core (FR-003). 30-minute inactivity session expiry, custom-built since WP core has no idle-timeout of its own (FR-006). Only Portal Admin accounts create/remove employee accounts (FR-009). No direct DB access beyond WordPress core APIs and this plugin's gated queries. |
| VI | Candidate Data Retention | No | N/A — no candidate data in this feature. |
| VII | Performance and Scale Targets | Yes | PASS. Login/landing pages are lightweight forms and lists, no heavy admin framework; same <3s bar and no new front-end payload added to public pages that don't use them. |
| VIII | Phase Discipline — No Scope Creep | Yes | PASS. Candidate database, search/filter, CV handling, and the Pending Review workflow (Principle III) are explicitly out of scope per spec.md's Assumptions and stay that way in this plan. |

**Gate result**: PASS. No violations to justify — Complexity Tracking table is empty.

**Post-design re-check** (after Phase 1 — data-model.md, contracts/,
quickstart.md): No new entities, dependencies, or integrations were
introduced during design beyond what Technical Context already declared (WP
core users/roles/usermeta, the `eminence-portal` plugin, no external
services). Gate result unchanged: **PASS**.

## Project Structure

### Documentation (this feature)

```text
specs/011-employee-login/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output
│   └── portal-auth-contract.md
└── tasks.md              # Phase 2 output (/speckit-tasks — not created by this command)
```

### Source Code (repository root)

This feature is a new WordPress plugin, kept separate from the
`eminence-consultant` theme per constitution Principle I. The theme keeps
exactly one small integration point — the existing login page template hands
off to the plugin instead of rendering static placeholder copy.

```text
wp-content/
├── plugins/
│   └── eminence-portal/
│       ├── eminence-portal.php        # Plugin bootstrap: header comment, hooks,
│       │                              # requires for the files below
│       ├── includes/
│       │   ├── roles.php              # Activation hook: registers eminence_recruiter
│       │   │                          # and eminence_portal_admin roles + capabilities
│       │   ├── capabilities.php       # Capability constants + current_user_can()
│       │   │                          # gate helpers, reused everywhere access is checked
│       │   ├── auth.php               # Login form handling (wp_signon() wrapper),
│       │   │                          # generic error messaging, sign-out
│       │   ├── session-timeout.php    # Last-activity tracking (user meta) + 30-minute
│       │   │                          # inactivity enforcement on authenticated requests
│       │   ├── account-management.php # Admin-only create/list/deactivate employee
│       │   │                          # accounts, last-Admin-standing guard
│       │   └── shortcodes.php         # [eminence_employee_login] — the single entry
│       │                              # point the theme template calls
│       └── assets/
│           └── css/portal.css         # Login form + account-management styles
└── themes/
    └── eminence-consultant/
        └── page-employee-login.php    # Existing template (001) — updated to call
                                        # do_shortcode('[eminence_employee_login]')
                                        # instead of rendering static "coming soon" copy
```

**Structure Decision**: New plugin (`eminence-portal`), not theme code —
constitution Principle I is explicit that portal/employee-data features are a
custom plugin, and keeping auth/role logic out of the theme also means a
future theme redesign can never accidentally touch security-critical code.
The theme's existing login page template (from 001) is kept and simply
delegates to the plugin's shortcode, which is the smallest possible
integration surface between the two — the plugin owns every line of actual
logic, capability checking, and data access.

## Complexity Tracking

*No Constitution Check violations — this table is intentionally empty.*

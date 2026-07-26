# Implementation Plan: Site Shell & Global Navigation

**Branch**: `001-site-shell-navigation` | **Date**: 2026-07-26 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/001-site-shell-navigation/spec.md`

## Summary

Build the WordPress theme foundation that every public page renders inside: header with
primary navigation, footer with social links + Privacy Policy link, mobile-responsive nav,
a styled 404 template, GA4 analytics wired behind a cookie-consent gate, an Employee Login
placeholder page, and native WordPress page editing so the business owner can update text
and images without a developer. No custom database tables — this feature is pure WordPress
theme (presentation layer) plus WordPress core's native content-editing and sitemap features.

## Technical Context

**Language/Version**: PHP 8.1+ (WordPress 6.x compatible), HTML5, CSS3, vanilla JavaScript
(ES6) — no JS framework, per Alternatives Considered in research.md.

**Primary Dependencies**: WordPress core 6.x (self-hosted). Custom theme
`eminence-consultant`, built from scratch (not a page-builder theme), registering standard
nav menu locations and using WordPress's native Page post type for editable content. One
lightweight, actively-maintained SEO plugin for per-page meta title/description fields
(see research.md for the specific choice and rationale) — no page-builder, no bundled
"all-in-one" suite. GA4 loaded via `gtag.js`, gated by a hand-rolled cookie-consent banner
(no consent-management plugin — see research.md).

**Storage**: MySQL (WordPress's own `wp_posts`/`wp_postmeta`/`wp_options` tables). No custom
tables for this feature — Navigation Links map to native WP Nav Menu items; Social Links map
to Theme Customizer settings (`theme_mods`). This is the correct scope boundary: MySQL tables
for candidate data belong to later Employee Portal modules, not this feature.

**Testing**: No unit-test framework for this feature — it is a presentation-layer WordPress
theme with no business logic to unit test. Validated via: (a) a manual QA checklist walking
every acceptance scenario in spec.md, (b) an automated Lighthouse audit (performance,
accessibility, SEO categories) against every page template, (c) an automated axe-core
accessibility scan, (d) responsive checks at 320px/768px/1280px+ viewport widths. See
quickstart.md.

**Target Platform**: Web, responsive (desktop/tablet/mobile). Latest Chrome, Firefox,
Safari, Edge (per constitution Technical Constraints).

**Project Type**: Web (WordPress theme).

**Performance Goals**: Every page load under 3 seconds on standard broadband/4G (constitution
Principle VII, spec FR-009/SC-004).

**Constraints**: No public access to the database layer beyond what WordPress itself exposes
through its standard front-end rendering (constitution Principle V). Basic WCAG compliance:
alt text on all images, readable contrast (constitution Technical Constraints, spec FR-010).
Non-essential analytics MUST NOT fire before cookie consent (spec FR-013).

**Scale/Scope**: 1 shared theme shell wrapping 9 pages total in this module (8 content pages
+ 1 Employee Login placeholder), plus a 404 template and a Privacy Policy page. Single
language, single region (India). No custom database entities.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| # | Principle | Applies? | Assessment |
|---|-----------|----------|------------|
| I | Fixed Technology Stack | Yes | PASS. WordPress + PHP + MySQL, as required. The SEO plugin is a narrowly-scoped addition to the fixed platform, not a stack substitution — see research.md for why it doesn't violate this principle. |
| II | Role-Gated Access to Candidate Data | No | N/A — this feature carries no candidate data. The Employee Login entry point is a placeholder page only; it exposes no data and performs no authentication. |
| III | Public/Internal Data Separation | No | N/A — no candidate records exist in this feature. |
| IV | Mandatory Duplicate Detection | No | N/A — no candidate records are created by this feature. |
| V | Security Baseline | Yes | PASS. HTTPS is enforced at the hosting/shell level for every page (foundational for all later modules). No database layer is exposed beyond WordPress's own front-end rendering. Credential/session rules don't apply — no login exists yet. |
| VI | Candidate Data Retention | No | N/A directly, but this feature's footer links to the Privacy Policy page (`010-privacy-policy-page`, FR-002) that states the 24-month retention rule — this feature must not omit that link. |
| VII | Performance and Scale Targets | Yes | PASS, with an explicit approach: WebP images with `loading="lazy"`, minimal/no page-builder overhead, server/browser caching, and a Lighthouse gate in quickstart.md to hold the <3s target measurably rather than by assertion. |
| VIII | Phase Discipline — No Scope Creep | Yes | PASS. Employee Login renders a placeholder only — no auth logic, no session handling, nothing from BRD §4.2's Phase 2 list is touched. |

**Gate result**: PASS. No violations to justify — Complexity Tracking table is empty.

**Post-design re-check** (after Phase 1 — data-model.md, contracts/, quickstart.md): No new
entities, dependencies, or integrations were introduced during design beyond what Technical
Context already declared (native WP nav menus, theme_mods, one SEO plugin, hand-rolled
consent script). Gate result unchanged: **PASS**.

## Project Structure

### Documentation (this feature)

```text
specs/001-site-shell-navigation/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md         # Phase 1 output
├── quickstart.md         # Phase 1 output
├── contracts/            # Phase 1 output
│   └── theme-shell-contract.md
└── tasks.md              # Phase 2 output (/speckit-tasks — not created by this command)
```

### Source Code (repository root)

This feature is a WordPress theme, not an application with a `src/`-style layout. The
`wp-content/themes/eminence-consultant/` directory is the deliverable; later modules add a
separate custom plugin (`wp-content/plugins/eminence-portal/`, per constitution Principle I)
for the Employee Portal — that plugin is out of scope for this feature and plan.

```text
wp-content/
└── themes/
    └── eminence-consultant/
        ├── style.css                  # Theme metadata (WordPress requirement)
        ├── functions.php              # Nav menu registration, script/style enqueue,
        │                              # GA4 + consent-banner enqueue, image size setup
        ├── header.php                 # <head>, primary nav, mobile menu toggle
        ├── footer.php                 # Footer nav, social links, Privacy Policy link
        ├── page.php                   # Default template for all WP Pages (About, What We
        │                              # Do, For Employers, For Candidates, Testimonials,
        │                              # Industry Leaders, Contact) — content pulled from
        │                              # the WP editor, per FR-014 CMS editability
        ├── front-page.php             # Home page template (hero + CTAs)
        ├── page-employee-login.php    # Employee Login placeholder template
        ├── 404.php                    # Styled 404 template
        ├── template-parts/
        │   ├── navigation.php         # Header nav markup (desktop + mobile)
        │   ├── footer-widgets.php     # Social links + Privacy Policy link
        │   └── cookie-notice.php      # Cookie/privacy consent banner markup
        ├── assets/
        │   ├── css/
        │   │   └── theme.css          # Mobile-first responsive styles
        │   └── js/
        │       ├── mobile-nav.js      # Hamburger menu toggle
        │       └── consent.js         # Cookie consent state + gated GA4 loading
        └── screenshot.png             # WP theme screenshot (admin UI requirement)
```

**Structure Decision**: Single WordPress theme, no separate frontend/backend split (this is
a server-rendered WP theme, not a decoupled/headless build — simplest option that satisfies
every FR in spec.md and matches constitution Principle I). `page.php` is a single shared
template for all standard content pages because every one of specs 003–009 has an identical
structural contract with this shell (see `contracts/theme-shell-contract.md`) and differs
only in body content, which WordPress's native editor already handles — a template per page
would be needless duplication.

## Complexity Tracking

*No Constitution Check violations — this table is intentionally empty.*

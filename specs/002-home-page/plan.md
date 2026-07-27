# Implementation Plan: Home Page

**Branch**: `002-home-page` | **Date**: 2026-07-27 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/002-home-page/spec.md`

## Summary

Finish the Home page: a hero (tagline, dual employer/candidate CTAs) and a key-services
summary, rendered by `front-page.php` inside the Site Shell. A hero was already sketched
into `front-page.php` during Module 1's visual-design pass (2026-07-26) with the headline/
subtitle hardcoded directly in PHP — this plan's main correction is moving that text into
Customizer settings so it satisfies constitution Principle I/spec 001 FR-014 (business
owner can edit public page content without a developer), which the hardcoded version
violated.

## Technical Context

**Language/Version**: PHP 8.1+ (WordPress 6.x), no new language/runtime.

**Primary Dependencies**: None new. Uses the existing `eminence-consultant` theme
(`front-page.php`, `functions.php`) — no new plugin, per constitution Principle I.

**Storage**: WordPress Page content (the Home page's `the_content()`, for the services
summary) + two new Customizer settings (`theme_mods`) for the hero headline and subtitle —
same mechanism already used for the header phone number, not a new pattern.

**Testing**: Manual QA against quickstart.md scenarios + the same Lighthouse/axe-core gates
as Module 1 (no unit-test framework applies to a presentation-only page).

**Target Platform**: Web, responsive — unchanged from Module 1.

**Project Type**: Web (WordPress theme), same project as Module 1.

**Performance Goals**: Home page loads in under 3 seconds (spec SC-002).

**Constraints**: Hero and services-summary content must be business-owner-editable without
a code deployment (constitution Principle I via spec 001 FR-014 — this page's content is
public page content and inherits that requirement).

**Scale/Scope**: One page (Home), extending the existing `front-page.php`.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| # | Principle | Applies? | Assessment |
|---|-----------|----------|------------|
| I | Fixed Technology Stack | Yes | PASS. WordPress + PHP, no new dependency. |
| II–IV | Candidate data principles | No | N/A — no candidate data on this page. |
| V | Security Baseline | Yes | PASS. No new attack surface; Customizer settings go through WordPress's own sanitization. |
| VI | Candidate Data Retention | No | N/A. |
| VII | Performance and Scale Targets | Yes | PASS. Hero adds one CSS gradient (no image request) and two short text fields — negligible weight; still gated by the same Lighthouse check as Module 1. |
| VIII | Phase Discipline | Yes | PASS. No CV submission, no client-requirement form, no auth — CTAs link only to existing pages. |

**Gate result**: PASS. No violations to justify.

**Post-design re-check**: Unchanged — no new dependencies introduced in Phase 1. PASS.

## Project Structure

### Documentation (this feature)

```text
specs/002-home-page/
├── plan.md
├── research.md
├── data-model.md
└── quickstart.md
```

No `contracts/` — this feature has no interface other than the Site Shell contract it
already depends on (`001-site-shell-navigation/contracts/theme-shell-contract.md`).

### Source Code (repository root)

```text
wp-content/themes/eminence-consultant/
├── front-page.php      # MODIFY: pull hero headline/subtitle from Customizer, not hardcoded PHP
└── functions.php        # MODIFY: register the two new Customizer settings
```

**Structure Decision**: No new files. This is a correction/extension of the existing
`front-page.php`, consistent with Module 1's pattern of Customizer settings for
business-owner-editable chrome content (the phone number precedent).

## Complexity Tracking

*No Constitution Check violations — this table is intentionally empty.*

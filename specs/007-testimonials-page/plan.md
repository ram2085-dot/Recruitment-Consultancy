# Implementation Plan: Testimonials Page

**Branch**: `007-testimonials-page` | **Date**: 2026-07-27 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/007-testimonials-page/spec.md`

## Summary

Unlike 003–006, this feature needs real template code: testimonials are a repeating,
structured entity with a hard consent gate (spec FR-008/FR-009) and a graceful "omit if
missing" render rule for logos and empty categories (FR-002/FR-003). A custom post type is
the right amount of structure here — worth contrasting with 003's leadership team and
004's services, which stayed as plain content because they don't repeat with enforced
metadata the way testimonials do.

## Technical Context

**Language/Version**: PHP 8.1+ (WordPress 6.x) — unchanged.

**Primary Dependencies**: None new (no plugin) — a custom post type registered in
`functions.php`, per constitution Principle I.

**Storage**: A new custom post type `eminence_testimonial` (WordPress's own `wp_posts`
table, no new database schema) + a non-hierarchical taxonomy `testimonial_type` (client /
candidate) + one post meta field for consent tracking.

**Testing**: Manual QA against quickstart.md.

**Target Platform**: Web, responsive — unchanged.

**Project Type**: Web (WordPress theme), same project.

**Performance Goals**: Page loads in under 3 seconds (spec SC-003).

**Constraints**: A testimonial MUST NOT be publishable without documented consent
(FR-008/SC-002) — this has to be enforced in code, not just editorial policy, or it's not a
real requirement.

**Scale/Scope**: One page template + one CPT + a handful of testimonial entries.

## Constitution Check

| # | Principle | Applies? | Assessment |
|---|-----------|----------|------------|
| I | Fixed Technology Stack | Yes | PASS — CPT via WordPress core APIs, no plugin. |
| II–IV, VI | Candidate data principles | Partial | A candidate testimonial names a real person, but this is public-facing content the candidate has *consented to publish* — not the confidential internal candidate database Principles II–IV govern. FR-009 keeps it to only what consent covers. |
| V | Security Baseline | Yes | PASS — no new public write surface; testimonials are authored in `wp-admin` only. |
| VII | Performance | Yes | PASS — a handful of CPT entries, negligible query cost. |
| VIII | Phase Discipline | Yes | PASS — no public submission form for testimonials; editorial-only. |

**Gate result**: PASS. **Post-design re-check**: see data-model.md — no new violations
introduced.

## Project Structure

### Documentation (this feature)

```text
specs/007-testimonials-page/
├── plan.md
├── research.md
├── data-model.md
└── quickstart.md
```

No `contracts/` — no interface beyond the Site Shell contract.

### Source Code (repository root)

```text
wp-content/themes/eminence-consultant/
├── functions.php              # MODIFY: register eminence_testimonial CPT + taxonomy +
│                              # consent meta box + save_post enforcement
├── page-testimonials.php      # NEW: Template Name "Testimonials" — queries the CPT,
│                              # renders client/candidate sections, omits empty ones
└── assets/css/theme.css       # MODIFY: testimonial card styles
```

**Structure Decision**: New page template (not `page.php`) because this page needs a CPT
query loop, not `the_content()`. Assigned to the Testimonials page via
`Template Name` in `wp-admin`.

## Complexity Tracking

*No Constitution Check violations — this table is intentionally empty.*

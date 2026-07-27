# Implementation Plan: Industry Leaders We've Met Page

**Branch**: `008-industry-leaders-page` | **Date**: 2026-07-27 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/008-industry-leaders-page/spec.md`

## Summary

Like Testimonials (007), this page needs a custom post type rather than plain content: a
gallery of photos with the same kind of hard consent gate (FR-008: a photo of an
identifiable individual can't be published without documented permission) plus an actual
interactive slider (spec Acceptance Scenario 3). The consent-gate mechanism built for 007
is refactored into a shared helper so both features enforce it identically instead of
duplicating the logic.

## Technical Context

**Language/Version**: PHP 8.1+ (WordPress 6.x) + vanilla JS for the slider — unchanged
stack, no framework (constitution/research.md #4 from Module 1 still applies).

**Primary Dependencies**: None new. A custom post type, reusing the consent-gate pattern
from `007-testimonials-page`.

**Storage**: A new custom post type `eminence_gallery_photo` (featured image + optional
caption via post title), reusing the same `eminence_consent_obtained` meta field name and
enforcement mechanism as testimonials (now shared via `eminence_register_consent_gate()`).

**Testing**: Manual QA against quickstart.md, same approach as 007.

**Target Platform**: Web, responsive — unchanged.

**Project Type**: Web (WordPress theme), same project.

**Performance Goals**: Page loads in under 3 seconds (spec SC-004).

**Constraints**: A photo of an identifiable individual MUST NOT be publishable without
consent (FR-008) — enforced in code, not editorial policy, same reasoning as 007. Every
image needs alt text (FR-007) — satisfied by WordPress's native attachment alt-text field,
which `the_post_thumbnail()` already outputs.

**Scale/Scope**: One page template + one CPT + a slider (CSS scroll-snap + a small JS file
for prev/next buttons — no carousel library).

## Constitution Check

| # | Principle | Applies? | Assessment |
|---|-----------|----------|------------|
| I | Fixed Technology Stack | Yes | PASS — CPT + vanilla JS, no plugin, no framework. |
| II–IV, VI | Candidate data principles | No | N/A — these are photos of industry contacts, not the internal candidate database. |
| V | Security Baseline | Yes | PASS — no new public write surface. |
| VII | Performance | Yes | PASS — CSS scroll-snap has no JS-framework overhead; images should be reasonably sized (editorial responsibility, same as any content image). |
| VIII | Phase Discipline | Yes | PASS — no public submission form. |

**Gate result**: PASS. **Post-design re-check**: see data-model.md — no new violations.

## Project Structure

### Documentation (this feature)

```text
specs/008-industry-leaders-page/
├── plan.md
├── research.md
├── data-model.md
└── quickstart.md
```

### Source Code (repository root)

```text
wp-content/themes/eminence-consultant/
├── functions.php                       # MODIFY: extract eminence_register_consent_gate()
│                                        # helper (used by both 007 and this feature),
│                                        # register eminence_gallery_photo CPT
├── page-industry-leaders.php           # NEW: Template Name "Industry Leaders" — tagline
│                                        # + slider of published gallery photos
├── assets/css/theme.css                # MODIFY: slider styles
└── assets/js/industry-leaders-slider.js # NEW: prev/next scroll-snap controls
```

**Structure Decision**: New page template + new small JS file, following the same pattern
Testimonials established rather than inventing a second approach.

## Complexity Tracking

*No Constitution Check violations — this table is intentionally empty.*

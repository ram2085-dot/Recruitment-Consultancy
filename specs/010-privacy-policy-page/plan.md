# Implementation Plan: Privacy Policy Page

**Branch**: `010-privacy-policy-page` | **Date**: 2026-07-27 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/010-privacy-policy-page/spec.md`

## Summary

Populate the Privacy Policy page using `page.php` — no new template code. The one real
task is data, not code: the page must be designated as the site's official privacy page
via WordPress's native `wp_page_for_privacy_policy` option, which is exactly what Module
1's footer/cookie-notice links already call through `get_privacy_policy_url()`. This was
already discovered and fixed during Module 1 testing (a WordPress-auto-created draft
"Privacy Policy" page was claiming the slug and being linked to instead of the real one) —
this plan just confirms the mechanism and adds the actual disclosure content.

## Technical Context

**Language/Version**: PHP 8.1+ (WordPress 6.x) — unchanged.

**Primary Dependencies**: None new.

**Storage**: WordPress Page content (`the_content()`) + the native
`wp_page_for_privacy_policy` option (already wired up in Module 1, not introduced here).

**Testing**: Manual QA against quickstart.md.

**Target Platform**: Web, responsive — unchanged.

**Project Type**: Web (WordPress theme), same project.

**Performance Goals**: Page loads in under 3 seconds (spec SC-005).

**Constraints**: The retention period stated on this page MUST match the 24-month rule now
recorded as constitution Principle VI — this is a content-accuracy requirement, not a code
one, but it's binding.

**Scale/Scope**: One page, no new files.

## Constitution Check

| # | Principle | Applies? | Assessment |
|---|-----------|----------|------------|
| I | Fixed Technology Stack | Yes | PASS — no new dependency. |
| II–IV | Candidate data principles | No | N/A directly — this page discloses policy, it doesn't handle candidate data itself. |
| V | Security Baseline | Yes | PASS. |
| VI | Candidate Data Retention | Yes | PASS — this page is where the 24-month rule gets published; content must match the constitution exactly (spec SC-002). |
| VII | Performance | Yes | PASS — plain content. |
| VIII | Phase Discipline | Yes | PASS. |

**Gate result**: PASS. **Post-design re-check**: PASS, unchanged.

## Project Structure

### Documentation (this feature)

```text
specs/010-privacy-policy-page/
├── plan.md
└── quickstart.md
```

No `research.md` — the one technical decision here (`get_privacy_policy_url()` over
`get_page_by_path()`) was already made and documented in Module 1's spec/tasks history, not
new to this plan.

### Source Code (repository root)

No new or modified files. `page.php` renders this page; the footer/cookie-notice links
(`001-site-shell-navigation`) already point to it correctly via `get_privacy_policy_url()`.

**Structure Decision**: Reuse `page.php` as-is; confirm the `wp_page_for_privacy_policy`
option points at this page.

## Complexity Tracking

*No Constitution Check violations — this table is intentionally empty.*

# Implementation Plan: Contact Us Page (Layout Only)

**Branch**: `009-contact-us-page` | **Date**: 2026-07-27 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/009-contact-us-page/spec.md`

## Summary

Populate the Contact Us page (address, phone, email as static content with `tel:`/`mailto:`
links) using the existing `page.php` template. The one decision worth recording: whether
`tel:`/`mailto:` formatting needs template code or is an editorial task — it's editorial
(see research.md), same as the header phone number's `tel:` link is generated in code
because it's theme chrome, not page content.

## Technical Context

**Language/Version**: PHP 8.1+ (WordPress 6.x) — unchanged.

**Primary Dependencies**: None new.

**Storage**: WordPress Page content only (`the_content()`).

**Testing**: Manual QA against quickstart.md.

**Target Platform**: Web, responsive — unchanged.

**Project Type**: Web (WordPress theme), same project.

**Performance Goals**: Page loads in under 3 seconds (spec SC-002).

**Constraints**: MUST NOT include a functional contact form (spec FR-004) — that's a later
Client-Facing Forms module.

**Scale/Scope**: One page, no new files.

## Constitution Check

| # | Principle | Applies? | Assessment |
|---|-----------|----------|------------|
| I | Fixed Technology Stack | Yes | PASS — no new dependency. |
| II–IV, VI | Candidate data principles | No | N/A. |
| V | Security Baseline | Yes | PASS. |
| VII | Performance | Yes | PASS — plain content. |
| VIII | Phase Discipline | Yes | PASS — explicitly no form on this page (FR-004). |

**Gate result**: PASS. **Post-design re-check**: PASS, unchanged.

## Project Structure

### Documentation (this feature)

```text
specs/009-contact-us-page/
├── plan.md
├── research.md
└── quickstart.md
```

### Source Code (repository root)

No new or modified files.

**Structure Decision**: Reuse `page.php` as-is.

## Complexity Tracking

*No Constitution Check violations — this table is intentionally empty.*

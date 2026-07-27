# Implementation Plan: What We Do Page (Services + Industry Focus)

**Branch**: `004-what-we-do-page` | **Date**: 2026-07-27 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/004-what-we-do-page/spec.md`

## Summary

Populate the What We Do page (services list, featured Transformer industry, secondary
industries) using the existing `page.php` template — no new template code. The one design
decision is ensuring the Transformer industry is *visually distinct* from the secondary
list (spec FR-002), which is achievable with plain heading hierarchy/callout styling in
content, not a new template.

## Technical Context

**Language/Version**: PHP 8.1+ (WordPress 6.x) — unchanged.

**Primary Dependencies**: None new.

**Storage**: WordPress Page content only (`the_content()`).

**Testing**: Manual QA against quickstart.md.

**Target Platform**: Web, responsive — unchanged.

**Project Type**: Web (WordPress theme), same project.

**Performance Goals**: Page loads in under 3 seconds (spec SC-003).

**Constraints**: None beyond the existing shell constraints.

**Scale/Scope**: One page, no new files.

## Constitution Check

| # | Principle | Applies? | Assessment |
|---|-----------|----------|------------|
| I | Fixed Technology Stack | Yes | PASS — no new dependency. |
| II–IV, VI | Candidate data principles | No | N/A. |
| V | Security Baseline | Yes | PASS — no new attack surface. |
| VII | Performance | Yes | PASS — plain content. |
| VIII | Phase Discipline | Yes | PASS — no forms, no auth. |

**Gate result**: PASS. **Post-design re-check**: PASS, unchanged.

## Project Structure

### Documentation (this feature)

```text
specs/004-what-we-do-page/
├── plan.md
├── research.md
└── quickstart.md
```

No `data-model.md` — services and industries are plain content, not modeled entities (see
research.md). No `contracts/` — no interface beyond the existing Site Shell contract.

### Source Code (repository root)

No new or modified files. `page.php` (Module 1) renders this page's `the_content()` in
full.

**Structure Decision**: Reuse `page.php` as-is — a content-authoring task, per research.md.

## Complexity Tracking

*No Constitution Check violations — this table is intentionally empty.*

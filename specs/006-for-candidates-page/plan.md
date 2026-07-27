# Implementation Plan: For Candidates Page

**Branch**: `006-for-candidates-page` | **Date**: 2026-07-27 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/006-for-candidates-page/spec.md`

## Summary

Populate the For Candidates page (how the firm helps job seekers, career tips, CTA to
Contact Us) using the existing `page.php` template — no new template code, no new
entities. Pure content-authoring task, same pattern as 005.

## Technical Context

**Language/Version**: PHP 8.1+ (WordPress 6.x) — unchanged.

**Primary Dependencies**: None new.

**Storage**: WordPress Page content only (`the_content()`).

**Testing**: Manual QA against quickstart.md.

**Target Platform**: Web, responsive — unchanged.

**Project Type**: Web (WordPress theme), same project.

**Performance Goals**: Page loads in under 3 seconds (spec SC-002).

**Constraints**: MUST NOT include a CV submission form (spec FR-004) — that's the later
Public CV Submission & Review Workflow module.

**Scale/Scope**: One page, no new files.

## Constitution Check

| # | Principle | Applies? | Assessment |
|---|-----------|----------|------------|
| I | Fixed Technology Stack | Yes | PASS — no new dependency. |
| II–IV, VI | Candidate data principles | No | N/A — no candidate data collected on this page. |
| V | Security Baseline | Yes | PASS. |
| VII | Performance | Yes | PASS — plain content. |
| VIII | Phase Discipline | Yes | PASS — explicitly no CV form here (FR-004); that module comes later. |

**Gate result**: PASS. **Post-design re-check**: PASS, unchanged.

## Project Structure

### Documentation (this feature)

```text
specs/006-for-candidates-page/
├── plan.md
└── quickstart.md
```

No `research.md`/`data-model.md`/`contracts/` — no technical decisions beyond what
003/004/005 already established.

### Source Code (repository root)

No new or modified files.

**Structure Decision**: Reuse `page.php` as-is.

## Complexity Tracking

*No Constitution Check violations — this table is intentionally empty.*

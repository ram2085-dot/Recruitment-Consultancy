# Tasks: Home Page

**Input**: Design documents from `/specs/002-home-page/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, quickstart.md

**Tests**: No automated test framework applies (presentation-only page) — verified via
quickstart.md manual QA.

## Phase 1: Setup

- [x] T001 None — reuses the existing `eminence-consultant` theme from Module 1, no new
  dependency or scaffold needed.

## Phase 2: Foundational

- [x] T002 Register the "Homepage Hero" Customizer section with `eminence_hero_headline`
  and `eminence_hero_subtitle` settings (defaults = current placeholder copy) in
  `functions.php`

**Checkpoint**: Foundation ready.

## Phase 3: User Story 1 - First-Time Visitor Understands the Firm in Seconds (Priority: P1)

- [x] T003 [US1] Update `front-page.php` to read the hero headline/subtitle from
  `get_theme_mod()` instead of the hardcoded strings (depends on T002)
- [x] T004 [US1] Manual QA: execute quickstart.md scenarios 1–6

**Checkpoint**: Home page complete.

## Dependencies

T002 blocks T003. T003 blocks T004.

## Notes

- This is a small, single-story feature — no parallelizable task pairs worth calling out.

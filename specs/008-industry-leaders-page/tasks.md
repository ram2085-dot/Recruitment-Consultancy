# Tasks: Industry Leaders We've Met Page

**Input**: Design documents from `/specs/008-industry-leaders-page/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, quickstart.md

**Tests**: No automated test framework applies — verified via quickstart.md manual QA.

## Phase 1: Setup

- [x] T001 None — extends the existing `eminence-consultant` theme.

## Phase 2: Foundational

- [x] T002 Refactor 007's meta-box + save_post consent logic into
  `eminence_register_consent_gate( $post_type, $box_title, $checkbox_label )` in
  `functions.php`; update the `eminence_testimonial` registration to call it (no behavior
  change for 007 — verified by re-running 007's consent-gate test after the refactor)
- [x] T003 [P] Register the `eminence_gallery_photo` custom post type (title, thumbnail
  support) in `functions.php`, calling `eminence_register_consent_gate()` for it

**Checkpoint**: CPT + shared consent-gate infrastructure ready.

## Phase 3: User Story 1 - Visitor Sees the Firm's Industry Network (Priority: P2)

- [x] T004 [US1] Create `page-industry-leaders.php` (Template Name: Industry Leaders):
  tagline + query published gallery photos, "coming soon" state when empty (depends on T003)
- [x] T005 [P] [US1] Add slider CSS (scroll-snap container, prev/next buttons) to
  `assets/css/theme.css`
- [x] T006 [P] [US1] Create `assets/js/industry-leaders-slider.js`: prev/next button
  `scrollBy()` handlers
- [x] T007 [US1] Enqueue the slider script, assign the template to the Industry Leaders
  page in `wp-admin` (depends on T004, T005, T006)
- [x] T008 [US1] Manual QA: execute quickstart.md scenarios 1–6, including the consent-gate
  enforcement test (4) and a re-check that 007's consent gate still works after the T002
  refactor (depends on T007). Found and fixed a real bug during this pass: the original
  post type slug `eminence_gallery_photo` (22 chars) exceeded WordPress's 20-character
  post-type-name limit, so `register_post_type()` silently returned a WP_Error the code
  never checked — the CPT never actually registered, though nothing errored visibly
  (the empty-state fallback rendered regardless, masking it). Renamed to `eminence_gallery`
  (16 chars). Post-fix: CPT registers (confirmed via `post_type_exists()`), the slider
  renders a sample photo with its caption, the empty "coming soon" state was confirmed
  before that fix, and the consent gate was confirmed via the same `wp eval`
  POST-simulation technique used for 007 (nonce + no checkbox → reverts to draft).
  007's consent gate re-confirmed working after the T002 refactor. Scenario 6 (375px
  viewport) not visually confirmed — no browser tool this session; CSS is written for it
  (horizontally scrollable slider, no page-level overflow).

**Checkpoint**: Industry Leaders page complete.

## Dependencies

T002 blocks T003. T003 blocks T004. T004–T006 block T007. T007 blocks T008.

## Parallel Example: Phase 3

```bash
Task: "Add slider CSS to assets/css/theme.css"
Task: "Create assets/js/industry-leaders-slider.js"
```

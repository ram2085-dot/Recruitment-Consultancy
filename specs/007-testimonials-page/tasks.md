# Tasks: Testimonials Page

**Input**: Design documents from `/specs/007-testimonials-page/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, quickstart.md

**Tests**: No automated test framework applies — verified via quickstart.md manual QA,
including the consent-gate enforcement (T007).

## Phase 1: Setup

- [x] T001 None — extends the existing `eminence-consultant` theme.

## Phase 2: Foundational

- [x] T002 Register the `eminence_testimonial` custom post type (title, editor, thumbnail
  support) in `functions.php`
- [x] T003 [P] Register the `testimonial_type` taxonomy (terms: client, candidate) in
  `functions.php`
- [x] T004 [P] Register the `eminence_consent_obtained` post meta field and its meta box
  (checkbox control) in `functions.php`

**Checkpoint**: CPT infrastructure ready.

## Phase 3: User Story 1 - Visitor Sees Proof of the Firm's Track Record (Priority: P2)

- [x] T005 [US1] Implement the `save_post` consent-gate hook: force status to `draft` if
  `eminence_consent_obtained` is not truthy (depends on T004)
- [x] T006 [US1] Create `page-testimonials.php` (Template Name: Testimonials): query
  published testimonials by `testimonial_type`, render client/candidate sections, omit
  empty sections entirely, render featured image only when present (depends on T002, T003)
- [x] T007 [US1] Add testimonial card styles to `assets/css/theme.css` (depends on T006)
- [x] T008 [US1] Assign the Testimonials template to the Testimonials page in `wp-admin`
- [x] T009 [US1] Manual QA: execute quickstart.md scenarios 1–7, including the consent-gate
  enforcement test (5) (depends on T005–T008). Scenarios 1–4 and 6 confirmed via HTTP/HTML
  inspection (client + candidate sections both render with their consented entries).
  Scenario 5 (the consent gate itself) confirmed via `wp eval` simulating the exact
  wp-admin POST the meta box submits (nonce + no checkbox → publish attempt reverts to
  draft; nonce + checkbox → publish succeeds) — a real exercise of the save_post hook, not
  a manual status edit. Scenario 7 (375px viewport) not visually confirmed — no browser
  tool available this session; the CSS grid (1 column below 768px) is written for it.

**Checkpoint**: Testimonials page complete.

## Dependencies

T002–T004 block T005/T006. T006 blocks T007/T008. T008 blocks T009.

## Parallel Example: Phase 2

```bash
Task: "Register testimonial_type taxonomy in functions.php"
Task: "Register eminence_consent_obtained post meta + meta box in functions.php"
```

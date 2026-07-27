# Tasks: Privacy Policy Page

**Input**: Design documents from `/specs/010-privacy-policy-page/`

**Prerequisites**: plan.md, spec.md, quickstart.md

**Tests**: No automated test framework applies — verified via quickstart.md manual QA.

## Phase 1: Setup

- [x] T001 None — reuses `page.php` from Module 1.

## Phase 2: Foundational

- [x] T002 Confirm `wp_page_for_privacy_policy` option points at this page (already set
  during Module 1 testing — verify it hasn't drifted)

## Phase 3: User Story 1 - Visitor Understands What Happens to Their Data (Priority: P1)

- [x] T003 [US1] Author Privacy Policy page content: data collected, 24-month retention
  (matching constitution Principle VI exactly), individual rights/contact route, cookies,
  third-party sharing, last-updated date (depends on T002)
- [x] T004 [US1] Manual QA: execute quickstart.md scenarios 1–7

**Checkpoint**: Privacy Policy page complete — all 10 Module 1 specs implemented.

## Dependencies

T002 blocks T003. T003 blocks T004.

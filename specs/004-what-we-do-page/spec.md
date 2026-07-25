# Feature Specification: What We Do Page (Services + Industry Focus)

**Feature Branch**: `004-what-we-do-page`

**Created**: 2026-07-25

**Status**: Draft

**Input**: User description: "Merged Services + Industries page, reflecting the firm's actual
scope: placement/staffing services (Permanent Staffing and Executive Search), with a primary
focus on the Transformer industry and secondary/other sectors served named alongside it. Content
only — renders inside the Site Shell (001); no forms or backend logic."

**Depends On**: `001-site-shell-navigation`

**Supersedes**: Previously planned separate "Services" and "Industries" pages — merged into one
page because the firm's actual service and industry scope is narrow enough (placement services,
concentrated in one primary industry) that splitting it across two pages would be thin content on
each.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Prospective Client Confirms Fit on Both Service and Industry (Priority: P1)

A hiring manager wants to know, in one place, both what recruitment services the firm provides
and whether the firm has real depth in their industry — especially if they're hiring in the
Transformer sector, the firm's core focus.

**Why this priority**: Directly supports the BRD objective of attracting clients. Service and
industry fit are usually evaluated together by a hiring manager, not as two separate research
steps — combining them into one page shortens the path to "yes, they're relevant to us."

**Independent Test**: Can be fully tested by loading the What We Do page as an anonymous visitor
and confirming both the services list and the industry focus (Transformer primary, others
secondary) render together — independent of any other page being finished.

**Acceptance Scenarios**:

1. **Given** a visitor opens the What We Do page, **When** the page loads, **Then** they see the
   firm's services listed: Permanent Staffing and Executive Search, each with a description.
2. **Given** a visitor wants to know the firm's industry depth, **When** they view the page,
   **Then** the Transformer industry is clearly presented as the firm's primary/featured focus
   (not just one item in an undifferentiated list).
3. **Given** a visitor is in a different sector the firm also serves, **When** they view the
   page, **Then** they see other served industries listed as secondary, alongside the Transformer
   focus.
4. **Given** a visitor wants to act after reading, **When** they reach the end of the page,
   **Then** they see a call-to-action directing them to Contact Us or For Employers.

---

### Edge Cases

- What happens if a visitor is only interested in industries, not services (or vice versa)? →
  Both sections are on the same page and independently scannable (clear headings), so a visitor
  can skip to the part relevant to them.
- What happens if the secondary industries list changes or grows later? → Secondary industries
  are structured as a simple, independently editable list, separate from the featured Transformer
  section, so entries can be added or removed without restructuring the page.
- What happens if final service descriptions or the secondary industries list aren't yet
  confirmed? → The BRD's draft values are used as placeholder content (see Assumptions) rather
  than leaving sections empty.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Page MUST list the firm's services: Permanent Staffing and Executive Search, each
  with a description.
- **FR-002**: Page MUST present the Transformer industry as the firm's primary/featured industry
  focus, visually distinct from the secondary industries list.
- **FR-003**: Page MUST list other industries the firm serves as secondary content alongside the
  Transformer focus.
- **FR-004**: Page MUST include a call-to-action directing visitors to Contact Us or For
  Employers.
- **FR-005**: Page MUST render inside the shared Site Shell (`001-site-shell-navigation`) with
  consistent header/footer navigation.
- **FR-006**: Page MUST include a unique page title and meta description for SEO.
- **FR-007**: Page MUST render responsively on desktop, tablet, and mobile viewports.
- **FR-008**: All images on the page MUST have descriptive alt text.

### Key Entities *(include if feature involves data)*

- **Service**: Name and description (Permanent Staffing, Executive Search).
- **Featured Industry**: The Transformer industry — name and description of the firm's depth/
  experience in it.
- **Secondary Industry**: Name, and optional short description, for other sectors served.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% of listed services display both a name and a non-empty description.
- **SC-002**: A visitor can correctly identify, without prompting, that Transformer is the firm's
  primary industry focus after viewing the page (verified via content walkthrough/usability
  check).
- **SC-003**: The page loads in under 3 seconds on a standard broadband/4G connection.
- **SC-004**: The page renders without layout breakage at desktop, tablet, and mobile widths.

## Assumptions

- Service descriptions for Permanent Staffing and Executive Search are pending final copy from
  the business owner (BRD Section 9); placeholder professional copy is used until confirmed.
- The secondary industries list is not yet fully confirmed by the business owner (BRD Section
  12); the BRD's draft list (Wires & Cables, IT, Real Estate, Manufacturing, etc.) is used as
  placeholder secondary content pending confirmation, with Transformer as the confirmed primary
  focus.
- Depends on `001-site-shell-navigation` for header, footer, and navigation behavior. The shell's
  navigation label for this page is "What We Do" (previously separate "Services" and
  "Industries" nav entries — see note on that spec).

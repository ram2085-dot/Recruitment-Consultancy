# Feature Specification: Contact Us Page (Layout Only)

**Feature Branch**: `009-contact-us-page`

**Created**: 2026-07-25

**Status**: Draft

**Input**: User description: "The public Contact Us page: office address, phone number(s), and
email address(es) as static content. No functional contact form in this module — that form is a
separate later module. Renders inside the Site Shell (001)."

**Depends On**: `001-site-shell-navigation`

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Visitor Finds How to Reach the Firm (Priority: P1)

Any visitor — client or candidate — who is ready to engage wants to quickly find the firm's
office address, phone number, and email address.

**Why this priority**: This is the baseline conversion point for every other page's
call-to-action; without reliable contact information, all upstream credibility-building work is
wasted.

**Independent Test**: Can be fully tested by loading the Contact Us page as an anonymous visitor
and confirming the address, phone, and email render as static, correct content — independent of
the functional contact form, which is out of scope for this spec.

**Acceptance Scenarios**:

1. **Given** a visitor opens the Contact Us page, **When** the page loads, **Then** they see the
   firm's office address.
2. **Given** a visitor opens the Contact Us page, **When** the page loads, **Then** they see the
   firm's phone number(s).
3. **Given** a visitor opens the Contact Us page, **When** the page loads, **Then** they see the
   firm's email address(es).
4. **Given** a visitor taps the phone number or email on a mobile device, **When** they interact
   with it, **Then** the device's native call/email action is triggered (`tel:`/`mailto:` links).

---

### Edge Cases

- What happens if a visitor expects to submit a message directly from this page? → In this
  module, only static contact details are shown; the functional contact form is explicitly out
  of scope here and delivered in a later module.
- What happens if the firm has multiple office locations or multiple phone/email contacts? →
  Layout supports listing more than one of each without visual crowding.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Contact Us page MUST display the firm's office address(es) as static content.
- **FR-002**: Contact Us page MUST display the firm's phone number(s) as static content, using
  `tel:` links.
- **FR-003**: Contact Us page MUST display the firm's email address(es) as static content, using
  `mailto:` links.
- **FR-004**: Contact Us page MUST NOT include a functional contact form in this module.
- **FR-005**: Contact Us page MUST render inside the shared Site Shell
  (`001-site-shell-navigation`) with consistent header/footer navigation.
- **FR-006**: Contact Us page MUST include a unique page title and meta description for SEO.
- **FR-007**: Contact Us page MUST render responsively on desktop, tablet, and mobile viewports.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% of listed contact details (address, phone, email) are accurate and correctly
  linked (`tel:`/`mailto:`).
- **SC-002**: The page loads in under 3 seconds on a standard broadband/4G connection.
- **SC-003**: The page renders without layout breakage at desktop, tablet, and mobile widths.

## Assumptions

- The functional contact form with email notification to the business owner (BRD Section 7.1) is
  explicitly out of scope for this spec and is delivered as part of the later Client-Facing Forms
  module.
- Final office address, phone number(s), and email address(es) are pending confirmation from the
  business owner (BRD Section 9); placeholder contact details are used until confirmed.
- Depends on `001-site-shell-navigation` for header, footer, and navigation behavior.

# Feature Specification: Privacy Policy Page

**Feature Branch**: `010-privacy-policy-page`

**Created**: 2026-07-25

**Status**: Draft

**Input**: User description: "A public Privacy Policy page explaining what personal data the site
collects, why, how long it is kept, and how a person can request access or deletion. Required
because the site runs analytics cookies from launch and later modules collect candidate personal
data (CVs, contact details, salary information) from Indian residents. Content only — renders
inside the Site Shell (001)."

**Depends On**: `001-site-shell-navigation`

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Visitor Understands What Happens to Their Data (Priority: P1)

A visitor — especially a candidate about to hand over their CV, phone number, and salary details
in a later module — wants to know what the firm will do with their information before they share
it.

**Why this priority**: The site targets Indian residents and processes personal data from launch
(analytics cookies) and increasingly thereafter (candidate submissions). Publishing this page is
a legal and trust prerequisite, not a nice-to-have — and the cookie notice in the Site Shell
links here, so that requirement is unsatisfiable without this page.

**Independent Test**: Can be fully tested by loading the Privacy Policy page as an anonymous
visitor and confirming each required disclosure section is present and readable — independent of
any other content page or the candidate-submission modules being finished.

**Acceptance Scenarios**:

1. **Given** a visitor opens the Privacy Policy page, **When** the page loads, **Then** they see
   what categories of personal data the firm collects and for what purpose.
2. **Given** a visitor opens the page, **When** they read it, **Then** they see how long personal
   data is retained and on what basis it is deleted.
3. **Given** a visitor opens the page, **When** they read it, **Then** they see how to contact the
   firm to request access to, correction of, or deletion of their personal data.
4. **Given** a visitor opens the page, **When** they read it, **Then** they see what cookies and
   analytics the site uses and how to decline non-essential ones.
5. **Given** a visitor is anywhere on the site, **When** they look at the footer or the cookie
   notice, **Then** they can reach the Privacy Policy page in one click.

---

### Edge Cases

- What happens if the firm's data retention period has not yet been decided (it is an open item
  in BRD Section 12)? → The page MUST NOT be published with a blank or vague retention statement;
  the business owner must confirm a specific period before launch. See Assumptions.
- What happens when the policy changes after launch? → The page shows a "last updated" date so
  visitors can tell which version they read.
- What happens if a visitor requests deletion of data held in the internal candidate database
  (later module)? → This page states the contact route for such requests; the internal handling
  process itself belongs to the Employee Portal modules, not this spec.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Page MUST state what categories of personal data the firm collects (site analytics
  data at launch; candidate-submitted data such as name, contact details, CV, and salary
  information once later modules ship) and the purpose of each.
- **FR-002**: Page MUST state how long personal data is retained and the basis on which it is
  deleted.
- **FR-003**: Page MUST state how an individual can request access to, correction of, or deletion
  of their personal data, including a working contact route.
- **FR-004**: Page MUST describe the cookies and analytics used by the site and how a visitor can
  decline non-essential cookies.
- **FR-005**: Page MUST state whether personal data is shared with any third party (e.g. client
  employers receiving candidate profiles) and under what circumstances.
- **FR-006**: Page MUST display a "last updated" date.
- **FR-007**: Page MUST be reachable in one click from the footer of every page and from the
  cookie notice (see `001-site-shell-navigation`).
- **FR-008**: Page MUST render inside the shared Site Shell (`001-site-shell-navigation`) with
  consistent header/footer navigation.
- **FR-009**: Page MUST include a unique page title and meta description for SEO.
- **FR-010**: Page MUST render responsively on desktop, tablet, and mobile viewports.
- **FR-011**: Page MUST be written in plain language understandable by a non-lawyer.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% of the required disclosures (data collected, purpose, retention, individual
  rights and contact route, cookies, third-party sharing) are present on the page.
- **SC-002**: The Privacy Policy is reachable in one click from every page on the site.
- **SC-003**: The page displays a "last updated" date that matches the most recent revision.
- **SC-004**: The page loads in under 3 seconds on a standard broadband/4G connection.
- **SC-005**: The page renders without layout breakage at desktop, tablet, and mobile widths.

## Assumptions

- **Blocking open item**: the candidate data retention period is unresolved in BRD Section 12.
  This page cannot be finalised until the business owner confirms a specific retention period.
  Development may proceed with placeholder text, but the page MUST NOT go live unconfirmed.
- The firm targets Indian residents, so this policy is written to satisfy India's Digital Personal
  Data Protection framework. Final wording should be reviewed by the business owner (and their
  legal advisor, if they have one) before launch — this spec defines what the page must disclose,
  not the legally binding wording itself.
- Terms of Use / Terms of Service is a separate concern and is not covered by this spec; the
  business owner may want it added later.
- Depends on `001-site-shell-navigation` for header, footer, cookie notice, and navigation
  behavior.

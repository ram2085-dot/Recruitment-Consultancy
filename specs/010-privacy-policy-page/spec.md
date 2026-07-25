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
links here, so that requirement is unsatisfiable without this page. It is also where the firm
commits publicly to its 24-month retention rule, which the candidate database must then honour.

**Independent Test**: Can be fully tested by loading the Privacy Policy page as an anonymous
visitor and confirming each required disclosure section is present and readable — independent of
any other content page or the candidate-submission modules being finished.

**Acceptance Scenarios**:

1. **Given** a visitor opens the Privacy Policy page, **When** the page loads, **Then** they see
   what categories of personal data the firm collects and for what purpose.
2. **Given** a visitor opens the page, **When** they read it, **Then** they see that candidate
   data is retained for 24 months from last activity and is deleted thereafter.
3. **Given** a visitor opens the page, **When** they read it, **Then** they see how to contact the
   firm to request access to, correction of, or deletion of their personal data.
4. **Given** a visitor opens the page, **When** they read it, **Then** they see what cookies and
   analytics the site uses and how to decline non-essential ones.
5. **Given** a visitor is anywhere on the site, **When** they look at the footer or the cookie
   notice, **Then** they can reach the Privacy Policy page in one click.

---

### Edge Cases

- What happens when a candidate's data reaches the end of the 24-month retention period? → It is
  deleted. This page states the policy; the mechanism that enforces deletion belongs to the
  Employee Portal / candidate database modules, not this spec.
- What happens if a candidate re-engages with the firm during the retention window? → The 24-month
  clock restarts from that activity, since retention runs from last activity rather than from the
  date the profile was created.
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
- **FR-002**: Page MUST state that candidate personal data is retained for 24 months from the
  candidate's last activity with the firm (last profile update or last engagement, whichever is
  later), after which it is deleted.
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
- **SC-002**: The stated retention period on the page matches the retention rule enforced by the
  candidate database (24 months from last activity) — the published policy and the system
  behaviour never diverge.
- **SC-003**: The Privacy Policy is reachable in one click from every page on the site.
- **SC-004**: The page displays a "last updated" date that matches the most recent revision.
- **SC-005**: The page loads in under 3 seconds on a standard broadband/4G connection.
- **SC-006**: The page renders without layout breakage at desktop, tablet, and mobile widths.

## Assumptions

- Candidate data retention was an open item in BRD Section 12. It was resolved on 2026-07-25:
  **24 months from the candidate's last activity** (last profile update or last engagement,
  whichever is later). This is now recorded in the project constitution so the Employee Portal
  modules, which enforce the actual deletion, inherit the same rule.
- The firm targets Indian residents, so this policy is written to satisfy India's Digital Personal
  Data Protection framework. Final wording should be reviewed by the business owner (and their
  legal advisor, if they have one) before launch — this spec defines what the page must disclose,
  not the legally binding wording itself.
- Terms of Use / Terms of Service is a separate concern and is not covered by this spec; the
  business owner may want it added later.
- Depends on `001-site-shell-navigation` for header, footer, cookie notice, and navigation
  behavior.

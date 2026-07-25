# Feature Specification: Public Static Website (Informational Pages)

**Feature Branch**: `001-public-static-pages`

**Created**: 2026-07-25

**Status**: Draft

**Input**: User description: "Module 1 of the Eminence Consultant recruitment website: the purely
informational/content pages of the public site (Home, About Us, Services, Industries, For
Employers, For Candidates, Testimonials, Industry Leaders We've Met, Contact Us layout, and an
Employee Login entry point). No forms, no data submission, no authentication logic — those are
covered by later modules."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Prospective Client Evaluates Firm Credibility (Priority: P1)

A hiring manager or business owner searching for a recruitment partner lands on the site and
needs to quickly understand what the firm does, which industries it serves, and why it should be
trusted, in order to decide whether to reach out.

**Why this priority**: This is the firm's primary revenue driver (BRD Objective: "Attract new
clients"). If a client visitor can't establish credibility and relevance within the first few
pages, the firm loses the lead before any contact ever happens.

**Independent Test**: Can be fully tested by navigating Home → About Us → Services → Industries →
For Employers as an anonymous visitor and confirming each page communicates firm identity,
service offering, sectors served, and process — deliverable and demonstrable with zero backend
functionality.

**Acceptance Scenarios**:

1. **Given** a visitor lands on the Home page, **When** the page loads, **Then** they see a hero
   banner, the firm's tagline, a summary of key services, and clear call-to-action buttons.
2. **Given** a visitor is on the Home page, **When** they navigate to About Us, **Then** they see
   the company story, mission, vision, leadership team, and a "why choose us" section.
3. **Given** a visitor wants to know what the firm offers, **When** they open the Services page,
   **Then** they see the list of services (e.g. Permanent Staffing, Executive Search) each with a
   description.
4. **Given** a visitor wants to know which sectors the firm serves, **When** they open the
   Industries page, **Then** they see the list of sectors served.
5. **Given** a visitor represents a hiring company, **When** they open the For Employers page,
   **Then** they see an explanation of how the recruitment process works and the client benefits
   (informational content only — no submission form on this page in this module).

---

### User Story 2 - Job Seeker Learns About the Firm (Priority: P2)

A job seeker researching recruitment firms wants to understand whether this firm places
candidates in their field and whether the firm is reputable, before deciding to engage further.

**Why this priority**: Supports the BRD objective of "building a candidate pipeline" — even
before any submission mechanism exists, the informational content is what convinces a candidate
the firm is worth engaging with.

**Independent Test**: Can be fully tested by navigating to For Candidates, Testimonials, and
Industry Leaders We've Met as an anonymous visitor and confirming the content renders correctly —
independent of any submission or portal functionality.

**Acceptance Scenarios**:

1. **Given** a visitor is a job seeker, **When** they open the For Candidates page, **Then** they
   see informational content and career tips (no CV submission form in this module).
2. **Given** a visitor wants proof of the firm's track record, **When** they open the
   Testimonials page, **Then** they see client and candidate success stories, and client logos
   where available.
3. **Given** a visitor wants to see the firm's industry connections, **When** they open the
   "Industry Leaders We've Met" page, **Then** they see the tagline "Building relationships with
   the people shaping India's workforce" and an image slider/gallery.

---

### User Story 3 - Any Visitor Finds Contact Info and Navigates on Any Device (Priority: P3)

Any visitor — client, candidate, or otherwise — wants to find the firm's contact details or reach
a specific page quickly, on whatever device they're using.

**Why this priority**: Baseline usability. Without reliable navigation and contact info, the
credibility and pipeline-building work of P1/P2 is wasted because visitors can't act on it or
reach a page.

**Independent Test**: Can be fully tested by loading every page on desktop, tablet, and mobile
viewport widths, confirming consistent navigation, and confirming the Contact Us page displays
office address, phone, and email — independent of any form submission logic.

**Acceptance Scenarios**:

1. **Given** a visitor is on any page, **When** they view the header/footer navigation, **Then**
   they can reach every other page in the site within 2 clicks.
2. **Given** a visitor wants to contact the firm, **When** they open Contact Us, **Then** they see
   the office address, phone number(s), and email address(es) as static content (no functional
   form in this module).
3. **Given** a visitor opens the site on a mobile phone or tablet, **When** any page loads,
   **Then** the layout adapts responsively with no broken elements or horizontal scrolling.
4. **Given** a visitor clicks the "Employee Login" entry point, **When** the placeholder page
   loads, **Then** they see a clear "coming soon" / placeholder message (full login is delivered
   in Module 2 — Employee Portal).

---

### Edge Cases

- What happens when a visitor navigates directly to the Employee Login URL before Module 2 is
  built? → Placeholder page must render cleanly, not a broken link or server error.
- How does the site handle a testimonial or industry-leader entry with a missing logo/photo? →
  Layout must degrade gracefully (e.g. omit the image slot) rather than showing a broken image.
- How does the site behave when final industries/services content is still pending confirmation?
  → Placeholder content (see Assumptions) renders in the interim; pages must not show empty
  sections or "[TBD]" text to real visitors.
- What happens on a very narrow viewport (e.g. 320px width)? → Navigation collapses to a mobile
  menu; no content is clipped or requires horizontal scrolling.
- What happens if a page is requested that doesn't exist? → A styled 404 page is shown, not a raw
  server error.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide the following pages: Home, About Us, Services, Industries, For
  Employers, For Candidates, Testimonials, Industry Leaders We've Met, Contact Us, and an Employee
  Login entry point.
- **FR-002**: Home page MUST include a hero banner, the firm's tagline, a summary of key
  services, and call-to-action buttons.
- **FR-003**: About Us page MUST include company story, mission, vision, leadership team, and a
  "why choose us" section.
- **FR-004**: Services page MUST list each service offered (e.g. Permanent Staffing, Executive
  Search) with a description.
- **FR-005**: Industries page MUST list the sectors served by the firm.
- **FR-006**: For Employers page MUST describe the recruitment process and client benefits as
  content only — no requirement-submission form in this module.
- **FR-007**: For Candidates page MUST provide informational/career-tips content — no CV
  submission form in this module.
- **FR-008**: Testimonials page MUST display client and candidate success stories, with client
  logos shown where available.
- **FR-009**: "Industry Leaders We've Met" page MUST display the tagline "Building relationships
  with the people shaping India's workforce" and an image slider/gallery.
- **FR-010**: Contact Us page MUST display office address, phone number(s), and email address(es)
  as static content — no functional contact form in this module.
- **FR-011**: System MUST provide an Employee Login entry point (nav link/button) that leads to a
  placeholder page in this module, since portal authentication is delivered in Module 2.
- **FR-012**: All pages MUST render responsively on desktop, tablet, and mobile viewports.
- **FR-013**: All pages MUST include basic on-page SEO: a unique page title, a meta description,
  and alt text on all images.
- **FR-014**: System MUST provide a sitemap for search engine indexing.
- **FR-015**: System MUST display social media links (e.g. in the footer).
- **FR-016**: Site navigation (header and footer) MUST be consistent across all pages, and every
  page MUST be reachable from Home within 2 clicks.
- **FR-017**: Every page MUST load in under 3 seconds under standard broadband/4G conditions.
- **FR-018**: All pages MUST follow basic WCAG practices: readable font sizing/contrast and alt
  text on all images.
- **FR-019**: System MUST show a styled 404 page for any non-existent URL.

### Key Entities *(include if feature involves data)*

- **Page Content**: A single informational page's editable content (title, body copy, images).
  One record per page listed in FR-001.
- **Service**: A service offered by the firm — name and description (e.g. "Permanent Staffing").
- **Industry/Sector**: A sector the firm serves — name, and optional short description.
- **Testimonial**: A client or candidate success story — author/company name, quote text, and an
  optional logo/photo.
- **Industry Leader Entry**: An image (and optional caption) shown in the "Industry Leaders We've
  Met" slider/gallery.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A first-time visitor can identify the firm's core service offering and target
  audience (clients vs. candidates) within the first screen of the Home page, without scrolling.
- **SC-002**: 100% of the site's pages load in under 3 seconds on a standard broadband/4G
  connection.
- **SC-003**: 100% of pages render without layout breakage or horizontal scrolling at desktop,
  tablet, and mobile viewport widths.
- **SC-004**: A visitor can reach any page on the site from the Home page in 2 clicks or fewer.
- **SC-005**: 100% of images across the site have descriptive alt text.
- **SC-006**: 100% of pages have a unique page title and meta description.

## Assumptions

- The industries/sectors list and services list are not yet finalized by the business owner (BRD
  Section 12). This module uses the BRD's draft values as placeholder content — Industries:
  Transformer, Wires & Cables, IT, Real Estate, Manufacturing (marked "etc." in the BRD, list may
  grow); Services: Permanent Staffing, Executive Search — and these MUST be confirmed and
  finalized with the business owner before production launch.
- The color palette is not yet finalized ("align with existing logo colors — to confirm with
  owner" per BRD Section 10). Development proceeds with a placeholder professional color scheme
  until the business owner confirms brand colors.
- Written content (About Us copy, leadership bios, testimonials, client logos, office contact
  details, professional photography) will be supplied by the business owner per BRD Section 9;
  placeholder/sample content may be used during development ahead of final delivery.
- Contact form submission, client requirement submission form, public CV submission form, and
  Employee Login authentication are explicitly out of scope for this module — they are covered by
  later modules (client-facing forms module and Employee Portal module).
- The production domain name is not yet registered (BRD Section 12); this module is built and
  validated on a staging/development URL.
- No e-commerce or payment functionality exists anywhere on these pages.

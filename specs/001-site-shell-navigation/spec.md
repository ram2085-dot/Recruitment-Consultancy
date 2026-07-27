# Feature Specification: Site Shell & Global Navigation

**Feature Branch**: `001-site-shell-navigation`

**Created**: 2026-07-25

**Status**: Draft

**Input**: User description: "Foundational cross-cutting shell for the public static website:
consistent header/footer navigation, responsive frame, basic SEO infrastructure (sitemap), 404
page, social media links, and the Employee Login navigation entry point (placeholder page only —
real authentication ships in Module 2). Every content page (Home, About Us, What We Do, For
Employers, For Candidates, Testimonials, Industry Leaders We've Met, Contact Us) renders inside
this shell."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Visitor Navigates the Site Consistently on Any Device (Priority: P1)

Any visitor, on any page, needs to be able to reach any other page of the site quickly and
predictably, on desktop or mobile, without hitting broken links or layout issues.

**Why this priority**: Every other page spec in this module depends on this shell existing —
without consistent navigation, none of the content pages are reachable or usable.

**Independent Test**: Can be fully tested by loading a single placeholder page wrapped in the
shell, confirming header/footer render, all nav links resolve (even to placeholder targets),
and the layout adapts across desktop/tablet/mobile — independent of any content page being
finished.

**Acceptance Scenarios**:

1. **Given** a visitor is on any page, **When** the page loads, **Then** a consistent header
   navigation is present linking to Home, About Us, What We Do, For Employers, For Candidates,
   Testimonials, Industry Leaders We've Met, and Employee Login.
2. **Given** a visitor is on any page, **When** the page loads, **Then** a consistent footer is
   present, including a Contact Us link and social media links.
3. **Given** a visitor is on the Home page, **When** they use the navigation, **Then** they can
   reach every other page within 2 clicks.
4. **Given** a visitor opens the site on a narrow viewport (e.g. a phone), **When** the page
   loads, **Then** the navigation collapses into a mobile-friendly menu with no clipped content
   or horizontal scrolling.
5. **Given** a visitor clicks "Employee Login" in the navigation, **When** the placeholder page
   loads, **Then** they see a clear "coming soon" message rather than a broken link or error.
6. **Given** a visitor navigates to a URL that doesn't exist, **When** the page attempts to load,
   **Then** a styled 404 page is shown with a link back to Home.
7. **Given** a visitor lands on any page, **When** the page loads, **Then** site traffic is
   recorded in the firm's analytics, and a cookie/privacy notice is presented with a link to the
   Privacy Policy page.

---

### User Story 2 - Business Owner Updates Site Content Without a Developer (Priority: P1)

The business owner needs to correct a phone number, swap a photo, or reword a section without
raising a request with a developer and waiting for a deployment.

**Why this priority**: Every content page spec assumes final copy arrives from the business owner
after build (BRD Section 9). Without owner-editable content, each of those handovers becomes a
developer task, and the site goes stale the moment the developer engagement ends.

**Independent Test**: Can be fully tested by logging into the content management interface as the
business owner, editing text and an image on any page, publishing, and confirming the change
appears on the live page — independent of any specific content page being finished.

**Acceptance Scenarios**:

1. **Given** the business owner is logged into the content management interface, **When** they
   edit text on any public page and publish, **Then** the change appears on the live page without
   developer involvement or a code deployment.
2. **Given** the business owner is logged into the content management interface, **When** they
   replace or add an image, **Then** they are able to set that image's alt text at the same time.

---

### Edge Cases

- What happens when a nav link points to a content page that isn't built yet (during phased
  rollout)? → Link must resolve to a graceful placeholder, never a raw 404 or broken link.
- What happens on a very narrow viewport (e.g. 320px width)? → Mobile menu handles all nav items
  without clipping or requiring horizontal scroll.
- What happens if a social media link's target platform/URL isn't yet confirmed? → Placeholder
  links are omitted or disabled rather than pointing to a dead or incorrect URL.
- What happens if a visitor declines non-essential cookies? → The site remains fully usable;
  non-essential analytics tracking is not activated for that visitor.
- What happens if the business owner edits content into a state that breaks layout (e.g. a very
  long heading, or an image of unexpected dimensions)? → The layout degrades gracefully rather
  than breaking, and the owner is not able to publish content that produces a broken page.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide a consistent header navigation, present on every page, linking
  to Home, About Us, What We Do, For Employers, For Candidates, Testimonials, Industry Leaders
  We've Met, and Employee Login.
- **FR-002**: System MUST provide a consistent footer, present on every page, including a Contact
  Us link, social media links, and a link to the Privacy Policy page (see
  `010-privacy-policy-page`). Contact Us is intentionally footer-only, not duplicated in the
  header nav (2026-07-27 — see Assumptions).
- **FR-003**: Every page MUST be reachable from Home within 2 clicks via the navigation.
- **FR-004**: Navigation MUST collapse into a mobile-friendly menu on narrow viewports without
  clipping content or requiring horizontal scrolling.
- **FR-005**: System MUST provide an Employee Login navigation entry point that leads to a
  placeholder "coming soon" page in this phase — full authentication is delivered in Module 2
  (Employee Portal).
- **FR-006**: System MUST display social media links (e.g. in the footer) [placeholder URLs
  pending business owner confirmation].
- **FR-007**: System MUST provide a sitemap for search engine indexing.
- **FR-008**: System MUST show a styled 404 page for any non-existent URL, with a link back to
  Home.
- **FR-009**: The shell (header, footer, and any page rendered inside it) MUST load in under 3
  seconds under standard broadband/4G conditions.
- **FR-010**: The shell MUST follow basic WCAG practices: readable font sizing/contrast, and alt
  text on all navigation/footer imagery (e.g. logo, social icons).
- **FR-011**: The header MUST display the firm's logo on every page, linking back to Home.
- **FR-012**: System MUST record page traffic in the firm's analytics on every public page.
- **FR-013**: System MUST present a cookie/privacy notice to first-time visitors, linking to the
  Privacy Policy page, and MUST NOT activate non-essential analytics tracking for a visitor who
  declines.
- **FR-014**: All public page content (text and images) MUST be editable by the business owner
  through a content management interface, without developer involvement or a code deployment.
- **FR-015**: The content management interface MUST allow the business owner to set alt text
  whenever they add or replace an image.
- **FR-016**: The header MUST display the firm's phone number as a `tel:` link, editable by the
  business owner without a code deployment, and MUST be omitted when no number is set (same
  omit-gracefully rule as Social Links, FR-006) rather than showing an empty or broken link.

### Key Entities *(include if feature involves data)*

- **Navigation Link**: A single nav item — label and target page/URL.
- **Social Link**: A social media reference — platform name and URL.
- **Header Phone Number**: A single phone number displayed in the header, editable by the
  business owner.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A visitor can reach any of the 8 content pages from Home in 2 clicks or fewer, on
  100% of pages tested.
- **SC-002**: Navigating to a non-existent URL shows a styled 404 page (never a raw server error)
  100% of the time.
- **SC-003**: Header and footer render without layout breakage or horizontal scrolling at
  desktop, tablet, and mobile viewport widths.
- **SC-004**: Any page wrapped in the shell loads in under 3 seconds on a standard broadband/4G
  connection.
- **SC-005**: The business owner can independently change text and replace an image on any public
  page, and see it live, without contacting a developer.
- **SC-006**: 100% of public page views are recorded in the firm's analytics.
- **SC-007**: A visitor who declines non-essential cookies can still use 100% of the public site.

## Assumptions

- This spec is a dependency for all content-page specs (002–010): each page assumes it renders
  inside this shell and does not redefine header/footer/navigation, analytics, cookie-notice, or
  content-editability behavior itself.
- The Privacy Policy page (`010-privacy-policy-page`) is linked from the footer rather than the
  main header navigation, which is standard practice and keeps the primary nav focused on the 8
  content pages.
- Contact Us is footer-only, not also in the header nav (per business-owner feedback,
  2026-07-27) — it had been in both, which read as redundant. Still reachable in 1 click from
  every page via the footer, so this doesn't affect FR-003/SC-001 (2-click reachability from
  Home).
- Services and Industries were originally planned as two separate nav entries/pages but were
  merged into a single "What We Do" page (see `004-what-we-do-page`) to match the firm's actual
  scope: placement services concentrated in the Transformer industry.
- The Employee Login page delivered here is a placeholder only; real login/authentication is
  covered by the separate Employee Portal module discussed later.
- Header phone number confirmed by the business owner (2026-07-26): +91 99995 03368.
- Social media account URLs are not yet confirmed by the business owner (BRD Sections 4.1, 9);
  placeholder or omitted links are used until confirmed.
- The production domain name is not yet registered (BRD Section 12); this shell is built and
  validated on a staging/development URL.

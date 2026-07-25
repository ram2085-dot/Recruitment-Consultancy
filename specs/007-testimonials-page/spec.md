# Feature Specification: Testimonials Page

**Feature Branch**: `007-testimonials-page`

**Created**: 2026-07-25

**Status**: Draft

**Input**: User description: "The public Testimonials page: client and candidate success
stories, with client logos shown where available. Content only — renders inside the Site Shell
(001); no forms or backend logic."

**Depends On**: `001-site-shell-navigation`

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Visitor Sees Proof of the Firm's Track Record (Priority: P2)

A prospective client or candidate wants third-party proof that the firm delivers results before
they commit to reaching out.

**Why this priority**: Social proof reinforces the credibility work done on Home/About Us and is
often the deciding factor for a hesitant visitor.

**Independent Test**: Can be fully tested by loading the Testimonials page as an anonymous
visitor and confirming testimonial entries render correctly, including graceful handling of
missing logos — independent of any other page being finished.

**Acceptance Scenarios**:

1. **Given** consented client testimonials exist, **When** a visitor opens the Testimonials page,
   **Then** they see client success stories.
2. **Given** consented candidate testimonials exist, **When** a visitor opens the Testimonials
   page, **Then** they also see candidate success stories.
3. **Given** a testimonial has an associated client logo, **When** the page renders, **Then** the
   logo is displayed alongside the testimonial.

---

### Edge Cases

- What happens when a testimonial has no associated logo? → The layout omits the logo slot
  gracefully rather than showing a broken image.
- What happens if no testimonials are available yet at launch? → The page shows a presentable
  "coming soon" state rather than an empty or broken layout.
- What happens if consent for a testimonial cannot be evidenced, or is later withdrawn? → That
  testimonial is not published, or is removed if already live.
- What happens if only client testimonials exist and no candidate ones (or vice versa)? → The
  page renders the available category cleanly without leaving an empty section heading.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Testimonials page MUST support and display two categories of success story: client
  and candidate.
- **FR-002**: Where a category has no consented testimonials available, the page MUST omit that
  category cleanly rather than showing an empty section heading.
- **FR-003**: Testimonials page MUST display the client's logo alongside a testimonial when a
  logo is available, and omit it gracefully when not.
- **FR-004**: Testimonials page MUST render inside the shared Site Shell
  (`001-site-shell-navigation`) with consistent header/footer navigation.
- **FR-005**: Testimonials page MUST include a unique page title and meta description for SEO.
- **FR-006**: Testimonials page MUST render responsively on desktop, tablet, and mobile
  viewports.
- **FR-007**: All images on the page, including client logos, MUST have descriptive alt text.
- **FR-008**: A testimonial MUST only be published where documented consent has been obtained
  from the individual or company named in it, covering both the quote and any logo, photo, name,
  or job title displayed with it.
- **FR-009**: Candidate testimonials MUST NOT disclose personal details beyond what the consent
  covers — specifically no contact details, salary information, or current employer beyond what
  the candidate has explicitly agreed to publish.

### Key Entities *(include if feature involves data)*

- **Testimonial**: Author/company name, quote text, type (client or candidate), and optional
  logo/photo.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% of testimonial entries render without broken images, regardless of whether a
  logo is present.
- **SC-002**: 100% of published testimonials have documented consent from the person or company
  named in them.
- **SC-003**: The page loads in under 3 seconds on a standard broadband/4G connection.
- **SC-004**: The page renders without layout breakage at desktop, tablet, and mobile widths.

## Assumptions

- Testimonial content and client logos are pending delivery from the business owner, with
  permission to publish (BRD Section 9); a presentable placeholder/coming-soon state is used
  until real testimonials are supplied.
- Obtaining and retaining evidence of consent is the business owner's responsibility; this spec
  requires that no testimonial is published without it, but does not define a consent-management
  system.
- Depends on `001-site-shell-navigation` for header, footer, and navigation behavior.

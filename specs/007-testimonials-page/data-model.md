# Phase 1 Data Model: Testimonials Page

## Testimonial (custom post type `eminence_testimonial`)

| Field | WordPress source | Notes |
|---|---|---|
| Author/company name | Post title | |
| Quote text | Post content (`the_content()` inside the loop) | |
| Type (client or candidate) | Taxonomy `testimonial_type`, term slugs `client`/`candidate` | Non-hierarchical, single-select in practice (one term per testimonial) |
| Logo/photo | Featured image | Optional — omitted gracefully in the template when absent (FR-003) |
| Consent obtained | Post meta `eminence_consent_obtained` (bool, `'1'`/`''`) | **Enforced**, not just recorded — see research.md #2. A testimonial cannot be `publish` status unless this is truthy. |

**Validation rules**:
- A testimonial's status is forced back to `draft` on save if
  `eminence_consent_obtained` is not truthy, regardless of what the editor tried to set
  (research.md #2).
- The template queries only `post_status = publish` testimonials, so an unconsented one
  (forced to draft) never renders publicly — this is what makes FR-008 a real gate rather
  than a checkbox nobody reads.

## Query shape the template needs

Two separate queries (or one query split by taxonomy term after fetching), each filtered
to `publish` status:
- `testimonial_type = client` → rendered under a "Client Testimonials" heading
- `testimonial_type = candidate` → rendered under a "Candidate Testimonials" heading

If either query returns zero results, that heading and section are omitted entirely
(FR-002/spec Edge Case) — never rendered as an empty heading.

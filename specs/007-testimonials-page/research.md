# Phase 0 Research: Testimonials Page

## 1. Custom post type vs. plain content vs. a plugin

**Decision**: A custom post type (`eminence_testimonial`) registered in `functions.php`,
with a non-hierarchical taxonomy (`testimonial_type`: client/candidate) and one post meta
field for consent tracking (`eminence_consent_obtained`).

**Rationale**: Testimonials repeat with enforced structure the spec requires code to
handle: FR-002/FR-003 say a category (client or candidate) with no entries must be omitted
cleanly, and FR-008/FR-009 require consent to be a hard gate, not editorial policy. A CPT
gives WordPress's own admin list/query APIs for this "for free" — no plugin needed.

**Alternatives considered**: (a) Plain page content, one block per testimonial (the 003/004
approach) — rejected, it can't enforce the consent gate or the "omit empty category"
behavior; both require querying by structured metadata, which unstructured content doesn't
have. (b) A testimonials plugin — rejected as disproportionate; WordPress's native
`register_post_type()`/`register_taxonomy()`/`register_post_meta()` cover everything this
spec needs.

## 2. How to enforce the consent gate (FR-008) in code, not just policy?

**Decision**: A meta box on the testimonial editor with a required "Consent obtained"
checkbox (`eminence_consent_obtained`). A `save_post` hook checks this on every save: if
the checkbox is unchecked and the post status is (or is being set to) `publish`, the hook
forces the status back to `draft` before the save completes.

**Rationale**: FR-008 says a testimonial "MUST only be published where documented consent
has been obtained" — if that's only a comment in the admin UI, an editor can still
publish without checking it, and the requirement is decorative. Enforcing it in
`save_post` makes it a real gate, matching how spec 001 treats similar hard requirements
(e.g. the GA4 consent gate) as code, not convention.

**Alternatives considered**: A required-field validation on the block editor's publish
button (client-side only) — rejected, trivially bypassable via the REST API or Quick Edit;
the server-side `save_post` hook is the only place this can't be skipped.

## 3. Logo/photo — featured image or a separate field?

**Decision**: WordPress's native featured image (`has_post_thumbnail()` /
`the_post_thumbnail()`), same mechanism `page.php` already uses for content-page images.

**Rationale**: No reason to introduce a second image-upload mechanism when the one the
theme already supports (Module 1's `add_theme_support('post-thumbnails')`) does the job —
and "no featured image set" is exactly the "omit gracefully" case FR-003/edge-case already
describes.

## Summary

No unresolved `NEEDS CLARIFICATION` markers.

# Phase 0 Research: About Us Page

## 1. How should the leadership team (FR-003) be authored?

**Decision**: Plain WordPress block-editor content — one "Media & Text" (or "Columns")
block per leadership member, inside the Page's normal `the_content()`. No custom post
type, no custom fields, no repeater plugin.

**Rationale**: The BRD gives no indication the leadership team is large, frequently
changing, or needs to be queried/filtered/reused elsewhere on the site — it's a handful of
people shown once, on one page. A custom post type or field group would be real,
unjustified complexity for that. Compare to `007-testimonials-page`, which does warrant a
custom post type: testimonials repeat with consent-tracking metadata and a graceful
omit-if-missing render rule the theme has to enforce in code, not just editorial content.

**Alternatives considered**: (a) Custom post type "Leadership Member" — rejected as
premature structure for a handful of static entries. (b) Advanced Custom Fields repeater —
rejected, same reasoning, plus it's a new plugin dependency for no functional gain over
plain blocks. Revisit only if the team is expected to grow large enough that consistent
per-member layout becomes hard to maintain by hand — not the case described anywhere in
the BRD.

## Summary

No unresolved `NEEDS CLARIFICATION` markers. No code changes required for this feature —
`page.php` (Module 1) already satisfies every functional requirement.

# Phase 0 Research: What We Do Page

## 1. Should services/industries be a modeled entity (custom fields/CPT) or plain content?

**Decision**: Plain WordPress block-editor content. The Transformer industry is set apart
visually using a heading level + a simple "featured" callout block (e.g. a colored
background/border via an existing button/group block styled with the theme's CSS classes),
not a new template or field group.

**Rationale**: This is a fixed, small set (2 services, one featured industry, a handful of
secondary ones) authored once and edited rarely. It doesn't need to be queried, filtered,
or reused elsewhere on the site, so a custom post type or repeater field would be
unjustified structure — same reasoning as `003-about-us-page`'s leadership team.

**Alternatives considered**: A "Service" custom post type — rejected, no functional need
(nothing else on the site queries services independently of this one page).

## Summary

No unresolved `NEEDS CLARIFICATION` markers. No code changes required — `page.php` already
satisfies every functional requirement.

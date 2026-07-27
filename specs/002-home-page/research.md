# Phase 0 Research: Home Page

## 1. Where should the hero headline/subtitle live so the business owner can edit them?

**Decision**: Two Customizer settings (`theme_mods`): `eminence_hero_headline` and
`eminence_hero_subtitle`, with the current placeholder copy as their defaults. Read by
`front-page.php` instead of the hardcoded strings written during the 2026-07-26 visual pass.

**Rationale**: Matches the existing precedent (`eminence_phone_number` in Module 1) rather
than introducing a second pattern. Avoids a new plugin (e.g. Advanced Custom Fields) for
two short text fields — proportionate to the actual need, consistent with constitution
Principle I's spirit of a controlled, understood stack.

**Alternatives considered**: (a) Leave hardcoded in PHP — rejected, this is exactly the
FR-014 violation this plan exists to fix. (b) A custom meta box on the Home page itself —
rejected, Customizer is simpler for two site-wide-feeling strings and keeps the pattern
identical to the phone number. (c) Advanced Custom Fields — rejected as disproportionate;
revisit only if the number of owner-editable structured fields grows enough that Customizer
becomes unwieldy (see 003/004/007's research for where that threshold gets crossed).

## 2. Should the "key services summary" section be separate from the Page's `the_content()`?

**Decision**: No — it stays inside `the_content()`, edited by the business owner in the
normal WordPress block editor, same as every other content page. Only the hero (headline,
subtitle, CTA buttons) is theme-templated.

**Rationale**: Spec FR-002 just requires "a summary of the firm's key services" — plain
prose/short blocks, not a structured repeating entity. There's nothing here that needs a
template loop the way Testimonials (007) or Industry Leaders (008) do.

**Alternatives considered**: A templated 3-column services-summary grid pulling from the
same data as `004-what-we-do-page` — rejected as premature; the spec doesn't ask for visual
parity between the two, and building a shared data source now would be scope creep ahead
of an actual requirement.

## Summary

No unresolved `NEEDS CLARIFICATION` markers.

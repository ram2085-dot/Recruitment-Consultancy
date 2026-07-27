# Phase 0 Research: Industry Leaders We've Met Page

## 1. Reuse the Testimonials consent-gate mechanism, or write a second one?

**Decision**: Extract 007's meta-box + `save_post` enforcement logic into one reusable
function, `eminence_register_consent_gate( $post_type, $box_title, $checkbox_label )`,
called once for `eminence_testimonial` and once for the new `eminence_gallery_photo` CPT.

**Rationale**: Both features need the exact same rule — a post can't be `publish` status
without a checked consent box, checked server-side in `save_post`, not just the admin UI.
Writing that twice would be the kind of duplication the "simplify" review pass would flag;
generalizing it now, while there are exactly two call sites and the shape is identical, is
proportionate — not speculative abstraction for a hypothetical third use.

**Alternatives considered**: A separate, near-identical block of code for this feature —
rejected as needless duplication now that the pattern is proven to repeat.

## 2. Slider implementation: library, custom carousel, or CSS scroll-snap?

**Decision**: CSS `scroll-snap-type` on a horizontally-scrolling flex container, with two
small JS-driven prev/next buttons that call `scrollBy()` — no carousel library, no
transform/animation logic to hand-roll.

**Rationale**: Matches Module 1's "no framework" decision (research.md #4 there): a
carousel library is unjustified weight for one gallery on one page, and native
`scroll-snap` gives smooth, accessible (keyboard/touch-scrollable) sliding behavior with
a fraction of the code a hand-rolled transform-based carousel would need.

**Alternatives considered**: A hand-rolled transform-based carousel (translateX per slide,
manual index tracking) — rejected, meaningfully more JS for equivalent user-facing
behavior. A slider library (Swiper, Slick) — rejected as a new dependency for a single-page
feature; revisit only if a second, more complex carousel need appears elsewhere on the
site.

## 3. Where does image alt text come from?

**Decision**: WordPress's native attachment alt-text field (`_wp_attachment_image_alt`),
already output by `the_post_thumbnail()`. No custom field.

**Rationale**: Same reasoning as `001-site-shell-navigation` FR-015 — the CMS already
provides an alt-text field at upload time; there's no reason to add a second one.

## Summary

No unresolved `NEEDS CLARIFICATION` markers.

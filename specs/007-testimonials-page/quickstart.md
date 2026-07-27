# Quickstart: Validating the Testimonials Page

## Prerequisites

- The Testimonials WordPress Page has the "Testimonials" template assigned
  (`page-testimonials.php`).
- At least one `eminence_testimonial` entry exists for each of client and candidate types,
  with consent checked and published.

## Validation scenarios

| # | Action | Expected Result |
|---|---|---|
| 1 | Load Testimonials | Client testimonials render under their own heading |
| 2 | Load Testimonials | Candidate testimonials render under their own heading |
| 3 | Add a testimonial with a featured image | Logo/photo displays alongside it |
| 4 | Add a testimonial with no featured image | Renders without a broken image |
| 5 | Create a testimonial, leave "Consent obtained" unchecked, try to publish | Post reverts to Draft — never appears on the live page |
| 6 | Unpublish all client testimonials, leaving only candidate ones | "Client Testimonials" heading disappears entirely; candidate section still renders |
| 7 | Load on a 375px-wide viewport | Testimonial cards stack without horizontal scroll |

## Done when

All 7 scenarios pass, including the consent-gate enforcement (5) — this is the one that
actually proves FR-008 is a real requirement, not editorial policy.

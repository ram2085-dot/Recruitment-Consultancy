# Quickstart: Validating the Industry Leaders We've Met Page

## Prerequisites

- The Industry Leaders page has the "Industry Leaders" template assigned.

## Validation scenarios

| # | Action | Expected Result |
|---|---|---|
| 1 | Load with zero gallery photos | Tagline still shows; a presentable "coming soon" state appears instead of an empty slider |
| 2 | Add a published gallery photo (with permission) | Tagline "Building relationships with the people shaping India's workforce" renders, slider shows the photo |
| 3 | Add a second photo, click next/prev | Slider scrolls smoothly between slides |
| 4 | Create a gallery photo, leave consent unchecked, try to publish | Reverts to Draft — never appears in the slider |
| 5 | Add a photo with no alt text set | Editor should still be prompted for alt text at upload (WordPress native behavior) |
| 6 | Load on a 375px-wide viewport | Slider remains usable (touch-scrollable), no horizontal page overflow |

## Done when

All 6 scenarios pass, including the consent-gate enforcement (4).

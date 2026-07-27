# Quickstart: Validating the About Us Page

## Prerequisites

- The About Us WordPress Page exists and has content authored per FR-001–FR-004: company
  story, mission/vision, leadership team (name, role, photo where consented), why-choose-us.

## Validation scenarios

| # | Action | Expected Result |
|---|---|---|
| 1 | Load About Us | Company story/history section is visible |
| 2 | Load About Us | Mission and vision statements are visible |
| 3 | Load About Us | Leadership team members show name + role; photos appear only where consent was obtained (FR-009) |
| 4 | Load About Us | "Why choose us" section with differentiators is visible |
| 5 | Remove a leadership member's photo in the editor | Layout degrades gracefully — no broken image icon |
| 6 | Load on a 375px-wide viewport | All four sections stack without horizontal scroll |

## Done when

- All 6 scenarios pass.
- Every image on the page has alt text set in the editor (spec FR-008).

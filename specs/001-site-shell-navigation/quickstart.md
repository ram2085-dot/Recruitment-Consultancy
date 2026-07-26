# Quickstart: Validating the Site Shell

This is a validation/run guide, not an implementation guide. It proves the shell satisfies
spec.md end-to-end. Full implementation steps belong in tasks.md.

## Prerequisites

- A local WordPress environment (e.g. `wp-env`, Local, or any PHP 8.1+ / MySQL stack) with
  WordPress core installed.
- The `eminence-consultant` theme placed in `wp-content/themes/` and activated.
- The chosen SEO plugin (research.md #1) installed and activated, with its sitemap module
  disabled.
- 9 WordPress Pages created (even with placeholder body text is fine at this stage): Home
  (set as the static front page), About Us, What We Do, For Employers, For Candidates,
  Testimonials, Industry Leaders We've Met, Contact Us, Employee Login, plus Privacy Policy.

## Setup

1. Activate the theme in `wp-admin → Appearance → Themes`.
2. `Appearance → Menus`: create a menu, add all 9 content pages (+ Employee Login) in the
   order from spec FR-001, assign it to the `primary` location. Create a second menu (or
   reuse it) assigned to `footer`.
3. `Appearance → Customize`: fill in the social platform URL fields (leave one blank
   deliberately, to test the omit-gracefully edge case).
4. Confirm GA4 measurement ID is configured (via `functions.php` constant or an options
   field — whichever the implementation lands on).

## Validation scenarios (map to spec.md Acceptance Scenarios)

| # | Command / Action | Expected Result |
|---|---|---|
| 1 | Load any page | Header nav present, links to all 9 destinations + Employee Login |
| 2 | Load any page | Footer present, shows social icons for every non-empty URL, omits the one left blank, links to Privacy Policy |
| 3 | From Home, click through nav | Every page reachable in ≤2 clicks |
| 4 | Resize viewport to 375px width | Nav collapses to hamburger menu; no horizontal scroll; menu opens/closes cleanly |
| 5 | Click "Employee Login" | Placeholder page loads with a clear "coming soon" message — no login form, no error |
| 6 | Visit a non-existent URL (e.g. `/does-not-exist`) | Styled 404 page renders with a link back to Home |
| 7 | Load any page with browser devtools Network tab open | No `gtag.js`/GA4 request fires before interacting with the consent banner |
| 8 | Click "Accept" on the consent banner | GA4 request fires; reload the page — banner does not reappear |
| 9 | Click "Decline" on the consent banner (fresh session) | No GA4 request fires on this or subsequent page loads; site remains fully usable |
| 10 | In `wp-admin`, edit any page's text and publish | Change appears on the live page immediately, no deploy step |
| 11 | In `wp-admin`, replace an image on any page | Editor prompts for alt text; alt text appears in the rendered `<img>` tag |

## Automated checks

- **Lighthouse** (Performance, Accessibility, SEO categories) against Home and at least one
  representative content page: Performance and Accessibility scores must support the <3s
  load target (spec SC-004) and WCAG basics (constitution Technical Constraints).
- **axe-core** scan against the same two pages: zero critical/serious violations.
- **Broken-link check** across the primary and footer nav: zero 404s for links that are
  supposed to resolve (the Employee Login placeholder is expected to resolve, not 404).

## Done when

- All 11 validation scenarios above pass.
- Lighthouse and axe-core checks pass their thresholds.
- `sitemap.xml` (`/wp-sitemap.xml`) is reachable and lists all 9 published pages.

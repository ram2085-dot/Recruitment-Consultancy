# Quickstart: Validating the Home Page

## Prerequisites

- The Site Shell (`001-site-shell-navigation`) is active and the Home page is set as the
  static front page.

## Validation scenarios

| # | Action | Expected Result |
|---|---|---|
| 1 | Load Home | Hero renders with headline, subtitle, and two CTA buttons |
| 2 | In `wp-admin` → Appearance → Customize → Homepage Hero, edit the headline | Change appears live on Home without a code deployment |
| 3 | Click "For Employers" CTA | Lands on the For Employers page |
| 4 | Click "For Candidates" CTA | Lands on the For Candidates page |
| 5 | Scroll past the hero | Key-services summary content (from the Page editor) is visible |
| 6 | Load on a 375px-wide viewport | Hero and CTAs stack without horizontal scroll |

## Done when

- All 6 scenarios pass.
- No hero text is hardcoded in `front-page.php` — confirm by editing the Customizer fields
  and seeing the live page change without touching a file.

# Phase 1 Data Model: Home Page

No custom database tables. One new conceptual entity, backed entirely by WordPress
Customizer settings (`theme_mods`) — same mechanism as the Site Shell's Social Link and
header phone number settings.

## Hero Content

**Maps to**: Two Customizer settings under a new "Homepage Hero" section registered in
`functions.php`.

| Field | WordPress source | Notes |
|---|---|---|
| Headline | `theme_mod` `eminence_hero_headline` | Sanitized as plain text (`sanitize_text_field`) |
| Subtitle | `theme_mod` `eminence_hero_subtitle` | Sanitized as plain text |

**Validation rules**: Both have a default value (the current placeholder copy) so the hero
never renders empty on a fresh install.

## Out of scope for this data model

- The "key services summary" section is not modeled here — it lives in the Home page's own
  `the_content()` (see research.md #2).
- CTA button targets (For Employers / For Candidates) are not editable content; they're
  resolved by slug lookup in `front-page.php`, unchanged from the existing implementation.

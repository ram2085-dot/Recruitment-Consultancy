# Phase 1 Data Model: Site Shell & Global Navigation

This feature introduces no custom database tables. Both entities identified in spec.md's
Key Entities section map onto WordPress's native constructs — there is nothing here for a
future developer to build as custom schema.

## Navigation Link

**Spec source**: spec.md Key Entities — "A single nav item — label and target page/URL."

**Maps to**: A native WordPress Nav Menu (`wp_nav_menu`) assigned to one of two theme
locations registered in `functions.php`:

| Field | WordPress source | Notes |
|---|---|---|
| Label | Menu item title (editable in Appearance → Menus) | Business owner can rename without a developer, satisfying FR-014 |
| Target | Menu item URL (Page link, or custom URL for the 404 fallback link) | Must resolve to a real Page or the Employee Login placeholder |

**Theme menu locations to register**: `primary` (header nav, FR-001) and `footer` (footer
nav, FR-002).

**Validation rules** (enforced editorially, not by code — WordPress nav menus don't support
custom validation hooks for this): every page listed in spec FR-001 must have a
corresponding menu item in `primary` before launch; QA checklist in quickstart.md checks
this rather than relying on runtime validation.

## Social Link

**Spec source**: spec.md Key Entities — "A social media reference — platform name and URL."

**Maps to**: WordPress Theme Customizer settings (`theme_mods`), one text field per
supported platform, registered in `functions.php` via `customize_register`.

| Field | WordPress source | Notes |
|---|---|---|
| Platform | Fixed set of customizer fields (e.g. LinkedIn, Facebook, Instagram, Twitter/X) | Platform list, not free-form — keeps the footer template simple |
| URL | Customizer text field value | Empty value means: omit that icon (spec Edge Case — placeholder links are omitted, not broken) |

**Validation rules**: `footer-widgets.php` MUST check each URL field is non-empty before
rendering that platform's icon — this is the mechanism that satisfies the spec's edge case
("social media link's target platform/URL isn't yet confirmed → omitted, not broken").

## Cookie Consent State (supporting concept, not a spec Key Entity)

Not persisted server-side. A single first-party cookie (e.g. `eminence_consent`) with value
`accepted` or `declined`, read by `consent.js` on every page load to decide whether to inject
the GA4 `gtag.js` snippet. No database involvement — deliberately out of WordPress's data
layer entirely, since it's per-browser state, not site content.

## Explicitly out of scope for this data model

Page *content* (headings, body copy, images) is not modeled here — it lives in WordPress's
built-in `wp_posts`/`wp_postmeta` tables via the standard Page post type, which every
downstream page spec (002–010) already assumes per its own "Depends On:
`001-site-shell-navigation`" note. This feature only owns the shell around that content.

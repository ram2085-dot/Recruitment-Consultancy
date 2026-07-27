# Phase 1 Data Model: Industry Leaders We've Met Page

## Gallery Photo (custom post type `eminence_gallery_photo`)

| Field | WordPress source | Notes |
|---|---|---|
| Caption | Post title (optional — may be empty) | |
| Image | Featured image | Alt text comes from the attachment's own alt-text field (research.md #3) |
| Consent obtained | Post meta `eminence_consent_obtained`, via the shared `eminence_register_consent_gate()` helper | **Enforced** — same mechanism as Testimonials (007); a post reverts to `draft` on save if unconsented |

**Validation rules**: Identical to Testimonials' consent rule (007 data-model.md) — a
`eminence_gallery_photo` cannot be `publish` status without consent, checked server-side.

## Query shape the template needs

One query: all published `eminence_gallery_photo` entries, ordered by menu order or date.
No taxonomy split needed (unlike Testimonials) — there's only one category of entry here.

If the query returns zero results, the template shows a "coming soon" state (spec Edge
Case) instead of an empty slider shell.

# Phase 0 Research: Contact Us Page

## 1. Are `tel:`/`mailto:` links (FR-002/FR-003) template code or editorial content?

**Decision**: Editorial. The business owner (or whoever authors this page) types the phone
number/email as a link in the block editor with `tel:+91...`/`mailto:...` as the URL — no
template code needed.

**Rationale**: This is genuinely different from the header phone number
(`001-site-shell-navigation`), which *is* template code because it's theme chrome rendered
on every page from a single Customizer setting. The Contact Us page's address/phone/email
is ordinary page content, authored once, on one page — exactly the same category as
About Us's story or What We Do's service descriptions, which are also plain content.

**Alternatives considered**: A dedicated Customizer/meta-field set for office address,
phone, email (mirroring the header phone number) — rejected; nothing else on the site needs
to reuse Contact Us's address/phone/email the way the header phone number is reused on
every page. Introducing that structure here would be solving a problem that doesn't exist.

## Summary

No unresolved `NEEDS CLARIFICATION` markers. No code changes required — `page.php`
already satisfies every functional requirement.

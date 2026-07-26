# Contract: Theme Shell → Content Page

This feature has no external/public API. Its "contract" is the interface every downstream
page spec (002-home-page, 003-about-us-page, 004-what-we-do-page, 005-for-employers-page,
006-for-candidates-page, 007-testimonials-page, 008-industry-leaders-page,
009-contact-us-page, 010-privacy-policy-page) can rely on without redefining it — matching
the "Depends On: `001-site-shell-navigation`" note already present in each of those specs.

## What the shell guarantees to every page

1. **Header** is rendered automatically above page content via `page.php` (or
   `front-page.php` for Home) calling `get_header()`. A content page template MUST NOT
   render its own `<header>` or duplicate primary navigation.
2. **Footer** is rendered automatically below page content via `get_footer()`, including the
   social links block and the Privacy Policy link. A content page template MUST NOT
   duplicate these.
3. **Page title / meta description**: the shell's `functions.php` wires the SEO plugin's
   fields into `<head>` automatically for any WordPress Page. A content page only needs its
   per-page title/description filled in via the editor (the plugin's meta box) — it does not
   need template code for this.
4. **Responsive frame**: `theme.css` provides the mobile-first grid/typography baseline
   every page renders inside. A content page's own styling should extend this, not replace
   it (e.g. don't reset viewport meta, don't override the breakpoint scale).
5. **Analytics + consent**: GA4 pageview tracking and the consent banner are injected
   site-wide by the shell (`consent.js`, gated `gtag.js`). A content page template MUST NOT
   add its own analytics snippet — that would risk double-firing or bypassing the consent
   gate (violates spec FR-013).
6. **404 fallback**: any URL that doesn't resolve to a published Page automatically renders
   `404.php`. Content pages do not need to handle "not found" themselves.
7. **CMS editability**: because content pages use the shared `page.php` template pulling
   from `the_content()`, any text/image the business owner edits in the WordPress editor
   appears live immediately — this is what satisfies each page spec's FR requiring the page
   to "render inside the shared Site Shell" and, transitively, FR-014 from this spec.

## What a content page spec must still define itself

- Its own body content structure (sections, headings, order) — the shell has no opinion on
  this beyond wrapping it.
- Its own images and their alt text (the shell provides the *ability* to set alt text via
  FR-015; each page is responsible for actually setting it, per that page's own alt-text FR
  added in the 2026-07-25 spec review).
- Any page-specific interactive element (e.g. the image slider on
  `008-industry-leaders-page`) — the shell provides no slider component.

## Stability

This contract is considered stable once `001-site-shell-navigation` reaches "Implemented"
status. A breaking change to header/footer markup, nav menu locations, or the consent
mechanism after that point requires re-checking every downstream page spec that depends on
it, not just this one.

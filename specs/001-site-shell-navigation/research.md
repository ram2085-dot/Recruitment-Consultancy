
# Phase 0 Research: Site Shell & Global Navigation

Each entry: **Decision** — **Rationale** — **Alternatives Considered**.

## 1. SEO plugin for per-page meta title/description

**Decision**: Use a single lightweight, actively-maintained SEO plugin (Rank Math *or*
Yoast SEO, free tier) purely for its per-page meta title/description editor fields and
Open Graph tags. Disable its XML sitemap module and use WordPress core's native sitemap
instead (see #2).

**Rationale**: Spec FR-006/SC-006 (via constitution Technical Constraints) requires every
page to have an editable meta title and description, and FR-014 requires the business owner
to edit this without a developer. WordPress core has no built-in UI for per-page meta
description — hand-rolling one is reinventing a well-solved, security-reviewed problem for
no benefit. A plugin here is a narrowly-scoped content-editing tool, not a stack
substitution, so it doesn't conflict with constitution Principle I (that principle governs
the CMS/platform/database, not every plugin).

**Alternatives considered**: (a) Hardcode meta tags per template in PHP — rejected, fails
FR-014 (owner can't edit without a developer). (b) A full "all-in-one" SEO/marketing suite —
rejected, larger attack surface and settings sprawl than this feature needs. (c) Build a
custom meta box — rejected, duplicates a mature, free, well-audited plugin for no gain.

## 2. Sitemap generation (FR-007)

**Decision**: Use WordPress core's native XML sitemap (`/wp-sitemap.xml`, built in since
WordPress 5.5) rather than a plugin-generated one.

**Rationale**: Core already satisfies FR-007 with zero added dependencies or maintenance
surface. Explicitly disable the SEO plugin's own sitemap module (#1) to avoid two competing
sitemaps confusing search engines.

**Alternatives considered**: Plugin-generated sitemap — rejected as redundant once core's is
in use; only worth it if the plugin's sitemap needs page-level exclusion controls, which this
9-page site does not.

## 3. Cookie/analytics consent (FR-006, FR-012, FR-013)

**Decision**: Hand-roll a small vanilla-JS consent banner (`consent.js` + a template part)
that stores the visitor's choice (accept/decline non-essential) in a first-party cookie, and
gate the GA4 `gtag.js` snippet behind that stored choice — the snippet is not injected into
the page at all until consent is recorded as accepted.

**Rationale**: The requirement is narrow (accept/decline non-essential cookies, block GA4
until accepted, link to Privacy Policy) and doesn't need a general-purpose consent-management
plugin's configurability. A ~50-line hand-rolled banner is easier to verify against FR-013
("MUST NOT activate non-essential analytics tracking for a visitor who declines") than
auditing a third-party plugin's behavior, and keeps plugin count down per Principle I's
spirit of a controlled, understood stack.

**Alternatives considered**: A consent-management plugin (e.g. CookieYes) — rejected as
disproportionate to a single analytics integration; revisit only if the site later adds
more third-party trackers (ad pixels, etc.), which are explicitly out of scope (constitution
Principle VIII, BRD §4.2).

## 4. Mobile navigation implementation

**Decision**: CSS (flexbox, mobile-first breakpoints) + a small vanilla-JS toggle
(`mobile-nav.js`) for the hamburger menu. No JS framework, no CSS framework (e.g. Bootstrap).

**Rationale**: The nav requirement (FR-004: collapse into a mobile menu without clipping or
horizontal scroll) is standard, well-understood front-end work that doesn't justify a
framework dependency. Keeping the theme framework-free minimizes page weight, which directly
supports the <3s load target (Principle VII) and keeps the theme auditable by a single
developer without a build pipeline.

**Alternatives considered**: A CSS framework (Bootstrap/Tailwind) — rejected, adds
kilobytes and a class-naming convention for a 9-page site that doesn't need a full utility
system. A JS framework (React/Vue) — rejected, this feature has no interactive state beyond
a menu toggle and a consent banner; a framework runtime would work against the performance
gate for no functional benefit.

## 5. Performance approach for the <3s target

**Decision**: WebP images with native `loading="lazy"` on below-the-fold images, minimal
render-blocking assets (theme.css and the two small JS files only — no page-builder runtime),
and a caching layer at the hosting level (e.g. object cache + a page-cache plugin or
host-level cache, chosen when hosting is selected — tracked as a dependency for the later
Deployment/Hosting module, not this feature).

**Rationale**: Constitution Principle VII makes the 3-second target binding, not aspirational,
so the plan needs a concrete mechanism, not just a promise. Avoiding a page-builder and
framework runtime (per #4) is the largest single lever available at the theme level; caching
is the second.

**Alternatives considered**: Relying on hosting alone without theme-level optimization —
rejected, a heavy page-builder theme can blow the budget regardless of hosting quality.

## 6. Employee Login placeholder implementation

**Decision**: A static WordPress Page (`page-employee-login.php` template) with a "coming
soon" message and no form, no login handler, and no session logic of any kind.

**Rationale**: Spec FR-005 and constitution Principle VIII require this to be a placeholder
only. Building it as a plain static page (rather than, say, a disabled login form) removes
any risk of accidentally shipping partial auth logic ahead of the Employee Portal module.

**Alternatives considered**: A disabled/greyed-out login form — rejected, it implies
functionality that doesn't exist yet and risks becoming a half-built auth surface that the
Employee Portal module then has to unpick.

## Summary

No unresolved `NEEDS CLARIFICATION` markers remain from the Technical Context in plan.md.

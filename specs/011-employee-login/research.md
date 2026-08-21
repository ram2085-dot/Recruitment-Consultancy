# Phase 0 Research: Employee Login & Authentication

## 1. Authentication mechanism

**Decision**: Use WordPress core's own user authentication — `wp_signon()` to
verify credentials and set the login cookie, `wp_hash_password()` /
`wp_check_password()` (phpass, upgrading to bcrypt-backed hashing on WP's own
schedule) for storage, `is_user_logged_in()` / `wp_get_current_user()` for
gating, `wp_logout()` for sign-out. Employee accounts are WordPress users.

**Rationale**: This is exactly what "no alternative framework" (constitution
Principle I) points toward — WordPress core already has an audited,
maintained credential store and cookie-auth system; building a parallel one
inside the plugin would mean maintaining our own password hashing and
session-cookie security code, which is the single highest-risk place to
introduce a bug in this entire project. Reusing it also means FR-003
("hashed and salted, never plaintext") is satisfied by construction, not by
custom code we have to get right ourselves.

**Alternatives considered**: A fully custom credential table + hand-rolled
`password_hash()`/session cookie — rejected, pure risk with no benefit over
WP core's own system for a WordPress-stack project. A third-party
authentication plugin (e.g., a "custom login form" plugin) — rejected, same
reasoning 001-site-shell-navigation used for cookie consent: a few hundred
lines of plugin code we own and understand beats an external dependency for
a narrowly-scoped need.

## 2. Role & capability model

**Decision**: Register two new custom roles on plugin activation —
`eminence_recruiter` and `eminence_portal_admin` — each with an explicit,
minimal capability set (`read` plus custom capabilities like
`eminence_manage_employees`, granted only to `eminence_portal_admin`).
WordPress's own built-in `administrator` role is left untouched and unused by
this feature.

**Rationale**: BRD Section 6.1's "Admin" is a business role ("can create/
remove employee accounts") — it is not the same thing as WordPress's
`administrator` capability set (plugin install/activation, theme editing,
site settings, every other user's account, etc.). Conflating the two would
mean every Portal Admin employee could also, say, deactivate the whole site.
Custom roles with only the capabilities this feature actually needs keep the
privilege surface matched to the spec.

**Alternatives considered**: Granting the real WP `administrator` role to
Portal Admin employees — rejected for the over-privilege reason above.
Using WP's built-in `subscriber`/`editor` roles and checking a custom user
meta flag for "is this person an Admin" instead of a real role — rejected,
capabilities are the mechanism WordPress itself expects access checks to use
(`current_user_can()`), and a parallel meta-flag check would be an easy spot
to forget in a future feature.

## 3. Session inactivity timeout (30 minutes)

**Decision**: Store a `eminence_last_activity` user-meta timestamp, updated
on every authenticated request. A `template_redirect` hook checks, for any
logged-in request, whether `time() - eminence_last_activity > 1800`; if so,
it calls `wp_logout()` and redirects to the login page with a
"session expired" message instead of letting the request proceed.

**Rationale**: WordPress core has no built-in idle/inactivity timeout — its
auth cookies are valid for a fixed duration (2 or 14 days) regardless of
activity, which does not satisfy FR-006. `template_redirect` is the
conventional WordPress hook for conditional redirects: it runs after the
query and current user are resolved but before any template output, so an
expired session never renders a single byte of authenticated content
(supports FR-005/SC-002 as well as FR-006). User meta is enough state; no new
database table is needed for a single timestamp per user.

**Alternatives considered**: A JavaScript-based idle timer (`setTimeout`
counting down in the browser, calling a logout endpoint) — rejected, adds
client-side complexity and a network dependency for something the server can
enforce simply and cannot be bypassed by disabling JS. Shortening WordPress's
native auth-cookie expiration to 30 minutes flat — rejected, that would log
an *active* employee out every 30 minutes regardless of activity, which
fails Acceptance Scenario 2 of User Story 2 (active use must NOT sign
someone out).

## 4. Failed-login lockout policy

*(Deferred to planning by spec.md's Edge Cases: "a hard lockout/rate-limit
threshold is left to the planning phase rather than fixed here.")*

**Decision**: After 5 consecutive failed login attempts for the same
account within a 15-minute window, that account is temporarily locked for 15
minutes — further attempts (even with the correct password) show the same
generic error used for any failed login (see §6), not a distinct
"locked out" message. Tracked via a WordPress transient keyed to the account,
no new database table.

**Rationale**: A brute-force guard is a reasonable, standard default for any
login form handling real credentials (this is exactly the kind of
"reasonable default" the spec's own Assumptions section anticipates) and
costs almost nothing to implement with a transient. Keeping the error message
generic even while locked out avoids adding a new way to distinguish "this
account exists and is locked" from "wrong credentials," preserving FR-004's
intent.

**Alternatives considered**: No lockout at all — rejected, leaves the login
form open to unlimited credential-guessing against a real employee account.
A third-party "limit login attempts" plugin — rejected for the same
narrowly-scoped-need reasoning as §1.

## 5. Account deactivation mechanism

**Decision**: A user-meta flag, `eminence_account_status` (`active` or
`deactivated`), checked in two places: (a) during login, via the
`authenticate` filter, rejecting a deactivated account's login attempt with
the same generic error as bad credentials; (b) on every authenticated
request, in the same `template_redirect` check used for session timeout —
covering the edge case where an account is deactivated mid-session.

**Rationale**: WordPress core doesn't ship an "account disabled" flag; a
dedicated user-meta value is the standard, low-code way to add one. Reusing
the generic error message at login (rather than a distinct "your account is
deactivated" message) avoids leaking account existence/status to whoever is
attempting the login, consistent with FR-004's spirit even though FR-004
itself is written about wrong-credentials specifically. Checking status on
every request (not just at login) is what makes Edge Case "deactivated while
mid-session" actually true rather than aspirational.

**Alternatives considered**: Deleting the WordPress user outright instead of
deactivating — rejected, spec's User Story 3 treats "remove/deactivate" as
one capability but deletion would also orphan any future audit/log records
tied to that user ID; deactivation is reversible and safer as a default,
deletion can still be exposed later if actually needed.

## 6. Where the Admin account-management screen lives

**Decision**: A capability-gated `wp-admin` submenu page (registered only if
the current user has `eminence_manage_employees`), not a hand-built
front-end screen. `eminence_portal_admin` gets baseline `read` access to
`wp-admin`; `eminence_recruiter` does not, so the menu item — and the
`wp-admin` area itself — is simply invisible/inaccessible to Recruiters,
satisfying FR-009 by construction rather than by an extra check to remember.

**Rationale**: `wp-admin` already provides secure, familiar patterns for
exactly this kind of screen — list tables, nonces on every form, capability
checks WordPress itself enforces before the page even loads — reusing it
means less custom code to get right for a security-sensitive feature.
Employees still authenticate through the front-end login page this spec
requires (FR-001); `wp-admin` access is only what a Portal Admin reaches
*after* signing in there, for this one screen.

**Alternatives considered**: A fully custom front-end account-management UI
(its own forms, its own CSRF/nonce handling, its own listing table) —
rejected, meaningfully more custom code for a feature where WordPress core
already solves the same problem, and more surface area for a mistake in a
NON-NEGOTIABLE-principle feature.

## 7. Employee sign-in error messaging

**Decision**: Exactly one generic message ("The email/password you entered
doesn't match our records.") is shown for: unknown identifier, wrong
password, deactivated account, and locked-out account. No wording, timing,
or behavioral difference between these cases is exposed to the person
submitting the form.

**Rationale**: Directly satisfies FR-004 and closes the account-enumeration
gap the deactivation (§5) and lockout (§4) decisions would otherwise reopen.

**Alternatives considered**: Distinct messages per failure reason (more
"helpful" to a legitimate employee who forgot their status) — rejected, an
attacker gets the same helpfulness, and BRD/constitution Principle V treats
account security as non-negotiable over convenience here.

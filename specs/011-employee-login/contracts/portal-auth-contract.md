# Contract: `eminence-portal` plugin ↔ theme, and ↔ future portal features

This feature has no public-facing API. It has two contracts: the small
interface point between the new plugin and the existing theme, and the
foundation this feature promises to any future Employee Portal feature
(most immediately, the candidate database) that will build on top of it.

## Plugin → Theme integration point

1. The theme's `page-employee-login.php` (from 001-site-shell-navigation)
   renders the shortcode `[eminence_employee_login]` in place of its current
   static "coming soon" copy. That is the *entire* integration surface — the
   theme does not call any plugin function directly, does not know about
   roles or capabilities, and does not render any auth-state-dependent
   markup itself.
2. The shortcode is responsible for all of:
   - Rendering the login form when nobody is authenticated.
   - Rendering the authenticated landing area (name + role) when someone is.
   - Rendering a "manage employee accounts" link to `wp-admin`, but only for
     an account holding `eminence_manage_employees` — never rendered at all
     for a Recruiter (FR-009).
   - Handling form submission (login, logout) and the redirects/messages
     that follow (invalid credentials, session-expired, signed-out).
3. The theme's header/footer wrap the shortcode's output exactly like any
   other page (per the 001 shell contract) — no special-casing for the
   login page beyond what 001 already does for the Employee Login nav item.

## What this feature guarantees to future Employee Portal features

Any later feature adding real portal functionality (candidate database,
search/filter, CV handling — Principles II-IV) can rely on, without
redefining:

1. **An authenticated request already exists to check.** `is_user_logged_in()`
   plus the two roles (`eminence_recruiter`, `eminence_portal_admin`) are the
   single source of truth for "who is this and what can they do" — a future
   feature adds capability checks against these same roles, it does not
   invent a third one.
2. **The 30-minute inactivity timeout already applies everywhere.** Because
   the `template_redirect` check this feature installs runs for any
   authenticated request site-wide (not just the login page), a future
   feature's pages inherit session expiry for free — they do not need their
   own idle-timeout logic.
3. **Deactivated accounts already can't act.** The same status check applies
   everywhere a logged-in user is present, so a future feature does not need
   to separately check `eminence_account_status`.
4. **Account provisioning is already solved.** New employees (Recruiters
   *and* any future specialized roles) are created through the existing
   Admin account-management screen — a future feature should not build a
   second way to create WordPress users.
5. **What this feature does NOT provide**: any capability related to
   candidate records themselves. `eminence_manage_employees` governs account
   management only; a future feature MUST define its own capability (e.g.
   something like `eminence_view_candidates`) rather than overloading this
   one, keeping "who can manage staff accounts" and "who can see candidate
   data" as separate, independently auditable permissions.

## Stability

This contract is considered stable once 011-employee-login reaches
"Implemented" status. Any change to the role slugs, capability names, or the
shortcode tag after that point requires re-checking every feature that
depends on it — most importantly the future candidate-database feature.

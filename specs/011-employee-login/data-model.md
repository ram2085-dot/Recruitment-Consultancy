# Phase 1 Data Model: Employee Login & Authentication

No new database tables. Every entity below is a WordPress core user, role, or
piece of user meta — see research.md §1-§5 for why.

## Employee Account

Backed by a WordPress user (`wp_users` + `wp_usermeta`), carrying one of the
two roles defined below.

| Field | Storage | Notes |
|---|---|---|
| Display name | `wp_users.display_name` | Native WP field. |
| Login identifier | `wp_users.user_login` (or `user_email`, see Open Question below) | What the employee types in to sign in. |
| Password | `wp_users.user_pass` | Hashed by `wp_hash_password()` (research.md §1). Never stored or logged in plaintext (FR-003). |
| Role | WP user role: `eminence_recruiter` or `eminence_portal_admin` | Exactly one of the two (FR-008). Not the built-in `administrator` role (research.md §2). |
| Account status | User meta: `eminence_account_status` = `active` \| `deactivated` | Defaults to `active` on creation. Checked at login and on every authenticated request (research.md §5). |
| Last activity | User meta: `eminence_last_activity` (Unix timestamp) | Updated on every authenticated request; drives the 30-minute inactivity check (research.md §3). |
| Failed-attempt tracking | Transient, keyed to the account | Not stored on the user record itself; expires on its own (research.md §4). |

**Validation rules**:
- Login identifier MUST be unique (WordPress enforces this natively for
  `user_login`/`user_email`).
- Role MUST be exactly one of the two custom roles — never both, never
  neither, never the built-in `administrator` (FR-008).
- A `eminence_portal_admin` account MUST NOT be deactivated, deleted, or
  demoted if it is the last active account holding that role (FR-010) — see
  Account Lifecycle below.

**State transitions** (account status):

```text
(created by Admin) -> active
active -> deactivated   [Admin action; blocked if this is the last active Portal Admin]
deactivated -> active   [Admin action, "reactivate"]
```

There is no self-service reactivation and no account deletes itself; every
transition is an explicit Admin action (FR-009).

## Role

A fixed set of exactly two WordPress roles, registered on plugin activation
(not stored as data the feature manages — this is configuration, not a
growing/queryable entity):

| Role slug | Business name (BRD 6.1) | Key capabilities |
|---|---|---|
| `eminence_recruiter` | Recruiter | `read` (baseline WP requirement for a logged-in user); no `wp-admin` access; no account-management capability. |
| `eminence_portal_admin` | Admin | `read`; `eminence_manage_employees` (gates the account-management screen, research.md §6). |

## Session

Not a custom entity/table — this is WordPress's own auth-cookie session,
augmented with the `eminence_last_activity` user meta above to add the
inactivity dimension WP core doesn't have natively (research.md §3).

| Concept | Storage |
|---|---|
| "Is this request authenticated?" | WordPress's own auth cookie + `is_user_logged_in()`. |
| "Has this session gone idle?" | `time() - eminence_last_activity > 1800` seconds, checked on `template_redirect`. |
| Ending a session | Explicit sign-out (`wp_logout()`, FR-007), inactivity timeout (FR-006), or the account being deactivated mid-session (Edge Case, checked the same way as timeout). |

## Open Question carried into implementation

Whether the login identifier is `user_login` (a distinct username) or
`user_email` (the employee's email address) is a UI/UX choice, not a data
model one — either maps cleanly to WordPress's existing unique-field
enforcement. Default to email, since Admins creating accounts already need
to know each employee's email and a separate invented username is one more
thing to communicate; `wp_signon()` accepts either without a schema change
either way.

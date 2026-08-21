# Quickstart: Validating Employee Login & Authentication

This is a validation/run guide, not an implementation guide. It proves the
feature satisfies spec.md end-to-end. Full implementation steps belong in
tasks.md.

## Prerequisites

- A local WordPress environment with the `eminence-consultant` theme active
  and the `eminence-portal` plugin placed in `wp-content/plugins/` and
  activated (activation registers the two custom roles — research.md §2).
- WP-CLI available, for scripting account creation/inspection without
  needing to click through `wp-admin` for every check.
- The Employee Login page (from 001-site-shell-navigation) exists and uses
  `page-employee-login.php`.

## Setup

1. Activate the `eminence-portal` plugin. Confirm the two roles exist:
   ```
   wp role list | grep eminence
   ```
   Expect `eminence_recruiter` and `eminence_portal_admin`.
2. Create the first Portal Admin account directly via WP-CLI (the
   "provisioned once, outside the self-service flow" bootstrap step from
   spec.md's Assumptions):
   ```
   wp user create admin.test admin.test@example.com --role=eminence_portal_admin --user_pass=TestPass123!
   wp user meta update admin.test eminence_account_status active
   ```
3. Confirm the login page renders the plugin's shortcode output (a login
   form), not the old static placeholder copy.

## Validation scenarios (map to spec.md Acceptance Scenarios)

| # | Story | Action | Expected Result |
|---|---|---|---|
| 1 | US1 / AS1 | Submit the login form with `admin.test` / `TestPass123!` | Redirected to the authenticated landing area; page shows the display name and role "Admin" |
| 2 | US1 / AS2 | Submit the login form with a wrong password | Single generic error shown; no indication of which field was wrong |
| 3 | US1 / AS3 | While logged out, request the authenticated landing area URL directly | Redirected to the login page; no authenticated content is ever rendered |
| 4 | US1 / AS4 | `wp user meta update admin.test eminence_account_status deactivated`, then attempt login with correct credentials | Login refused with the same generic error as Scenario 2 |
| 5 | US2 / AS1 | Log in, then `wp user meta update admin.test eminence_last_activity 0` to simulate 30+ idle minutes, then take any action | Signed out and redirected to login with a "session expired" message |
| 6 | US2 / AS2 | Log in, take an action every few minutes for 10+ minutes | Session stays active throughout |
| 7 | US3 / AS1 | As the Admin, create a new account with role `eminence_recruiter` | New account can immediately log in |
| 8 | US3 / AS2 | As the Admin, deactivate an existing (non-last-Admin) account | That account can no longer log in |
| 9 | US3 / AS3 | Log in as a Recruiter, request the account-management screen URL directly | Access denied; no account list or details rendered |
| 10 | US3 / AS4 | With exactly one active `eminence_portal_admin` account, attempt to deactivate/demote it | Action is blocked with an explanatory message; the account remains active and Admin |
| 11 | US4 / AS1 | While logged in, use the sign-out control | Session ends immediately; the authenticated landing area is no longer reachable without logging in again |

## Automated / scripted checks

- **Unauthenticated redirect sweep** (supports SC-002): script a `curl` loop
  against the authenticated landing area and the account-management URL with
  no session cookie; assert every response is a redirect to the login page,
  never a 200 with authenticated content.
- **Role-gate sweep** (supports SC-004): log in via `curl` as a Recruiter
  account (capture the auth cookies `wp_signon()`/`wp-login.php` sets) and
  assert the account-management URL still refuses access.
- **Timeout sweep** (supports SC-003): scripted variant of Scenario 5 above,
  asserting the redirect-with-message happens on the first request after the
  simulated idle period, not "eventually."
- **Password storage check** (supports FR-003): `wp db query "SELECT
  user_pass FROM wp_users WHERE user_login='admin.test'"` and confirm the
  value is a hash (starts with WordPress's hash prefix), never the literal
  password.
- **Last-Admin guard check** (supports SC-006): with only one active Admin,
  attempt deactivation via both the UI action and (if exposed) any
  underlying WP-CLI/database path a developer might be tempted to use
  directly, confirming the guard isn't only enforced in the UI layer.

## Cleanup

```
wp user delete admin.test --yes
```

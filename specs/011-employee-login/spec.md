# Feature Specification: Employee Login & Authentication

**Feature Branch**: `011-employee-login`

**Created**: 2026-08-19

**Status**: Draft

**Input**: User description: "Employee Login / Authentication (Module 2 foundation): replace the existing placeholder "Employee Login" page with real authentication for internal staff. Two roles per BRD Section 6.1 — Admin and Recruiter. Admin accounts can create/remove employee accounts (Security Baseline, constitution Principle V); Recruiters cannot manage accounts. Credentials stored hashed and salted, never plaintext. Sessions auto-expire after 30 minutes of inactivity. Login page itself stays public/unauthenticated (it's the gate), but everything behind it is role-gated and must never leak candidate PII to an unauthenticated or wrongly-roled request. This spec covers login, logout, session handling, account provisioning by Admins, and a minimal authenticated landing area confirming who's logged in and their role — it does NOT cover the candidate database, search/filter, or CV handling (Principles II-IV), which is separate follow-on scope once authentication exists to gate it."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Employee signs in (Priority: P1)

An employee (Admin or Recruiter) opens the Employee Login page — today a "coming soon" placeholder — enters their credentials, and reaches an authenticated landing area that confirms who they are and what role they hold. This replaces the placeholder with the actual front door to every future internal tool.

**Why this priority**: Nothing else in Module 2 can exist without this. It is the minimum slice that turns "Employee Login" from a placeholder into a real, working gate.

**Independent Test**: Can be fully tested by attempting to log in with a valid and then an invalid set of credentials and confirming the correct outcome each time — delivers a working authentication gate on its own, independent of account management or any other internal tool being built yet.

**Acceptance Scenarios**:

1. **Given** an active employee account and its correct credentials, **When** the employee submits them on the login page, **Then** they are signed in. A Recruiter lands on an authenticated landing area showing their name and role; an Admin lands directly on the account-management screen (their "dashboard" — 2026-08-21 refinement), since that is the one thing an Admin's login exists to reach.
2. **Given** incorrect credentials, **When** they are submitted, **Then** login is refused with a single generic error message that does not reveal whether the account exists or which field was wrong.
3. **Given** no active session, **When** anyone requests a URL that lives behind the login (including the authenticated landing area), **Then** they are redirected to the login page instead of seeing any internal content.
4. **Given** an account that has been deactivated by an Admin, **When** that person attempts to log in with their previously-correct credentials, **Then** login is refused.

---

### User Story 2 - Session ends after inactivity (Priority: P2)

A logged-in employee steps away from their device. After 30 minutes with no activity, their session ends on its own, so an unattended, unlocked screen doesn't leave internal systems exposed.

**Why this priority**: This is a named, non-negotiable line item in the project's Security Baseline, not a nice-to-have — but it only matters once User Story 1 (a real session to expire) exists.

**Independent Test**: Can be fully tested by logging in, waiting past the inactivity window without interacting with the site, then attempting an action — delivers verifiable session-timeout behavior independent of account management.

**Acceptance Scenarios**:

1. **Given** a logged-in employee who has taken no action for 30 minutes, **When** they next attempt any action that requires being logged in, **Then** they are signed out and redirected to the login page with a message explaining the session expired.
2. **Given** a logged-in employee actively using the site, **When** they take an action every few minutes, **Then** their session remains active and they are not signed out.

---

### User Story 3 - Admin provisions and removes employee accounts (Priority: P3)

An Admin creates an account for a new hire (as Admin or Recruiter) and removes access for someone who has left, without needing a developer or a deployment. Recruiters cannot do either.

**Why this priority**: Real employees can't sign in (User Story 1) until at least one account exists, and turnover has to be handled without manual database work — but this can be built and demoed after login itself works, using a temporary manually-created first Admin account.

**Independent Test**: Can be fully tested by signing in as an Admin, creating a new employee account, confirming that account can log in, then removing it and confirming it no longer can — delivers self-service account lifecycle management independent of any other internal tool.

**Acceptance Scenarios**:

1. **Given** a logged-in Admin, **When** they create a new employee account with a name, login identifier, role, and initial password, **Then** that person can immediately log in with those credentials.
2. **Given** a logged-in Admin, **When** they remove or deactivate an existing employee account, **Then** that account can no longer log in.
3. **Given** a logged-in Recruiter, **When** they attempt to reach account creation/removal, **Then** access is denied and no account list or account details are shown.
4. **Given** exactly one remaining active Admin account, **When** anyone attempts to remove or demote it, **Then** the system blocks the action so the team is never left with zero Admins.

---

### User Story 4 - Employee signs out (Priority: P4)

A logged-in employee explicitly signs out before leaving a shared or public computer, ending their session immediately rather than waiting for the 30-minute timeout.

**Why this priority**: A real convenience and security good practice, but the 30-minute timeout (User Story 2) already provides a backstop, so this is the smallest, lowest-risk piece and can land last.

**Independent Test**: Can be fully tested by logging in, clicking sign out, and confirming the previously-authenticated landing area is no longer reachable without logging in again.

**Acceptance Scenarios**:

1. **Given** a logged-in employee, **When** they choose to sign out, **Then** their session ends immediately and they are returned to the login page.

---

### Edge Cases

- What happens when a Recruiter directly requests an Admin-only URL (e.g., account management) by typing/pasting it? Access is denied the same as if the link were never shown — no partial account data is rendered before the check happens.
- What happens if someone repeatedly submits wrong credentials for the same account? The system does not reveal account existence through timing or error-message differences (see User Story 1, Scenario 2); a hard lockout/rate-limit threshold is left to the planning phase rather than fixed here.
- What happens if an Admin deactivates their own account while logged in? Treated the same as deactivating any other account — their own session ends on the next request, same as User Story 1 Scenario 4.
- What happens if two Admins edit the same account at the same time? Out of scope for this spec — the employee roster is small enough (per Assumptions) that this is not a launch-blocking scenario.
- What happens to a session if an employee's account is deactivated while they are mid-session? Their next action is treated as an unauthenticated request (same as Scenario 4) rather than waiting for the 30-minute timeout to catch it.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST replace the current placeholder Employee Login page with a functioning login form (identifier + password) at the same public URL.
- **FR-002**: The system MUST authenticate a submitted identifier/password pair against stored employee account credentials before granting access to anything behind the login.
- **FR-003**: The system MUST store employee passwords hashed and salted; plaintext passwords MUST NOT be stored anywhere (constitution Principle V).
- **FR-004**: The system MUST show a single generic error message for any failed login attempt, regardless of whether the identifier or the password was wrong.
- **FR-005**: The system MUST reject access to any authenticated page or capability for a request that does not have a valid, current session, redirecting it to the login page instead.
- **FR-006**: The system MUST end an employee's session automatically after 30 minutes with no activity from that employee (constitution Principle V).
- **FR-007**: The system MUST let a logged-in employee end their own session on demand (sign out).
- **FR-008**: The system MUST assign every employee account exactly one role, Admin or Recruiter (BRD Section 6.1), and MUST make that role visible to the employee on the authenticated landing area.
- **FR-009**: Only accounts with the Admin role MUST be able to create new employee accounts or remove/deactivate existing ones; the system MUST deny this capability to Recruiter accounts (constitution Principle V).
- **FR-010**: The system MUST prevent removal or demotion of the last remaining active Admin account.
- **FR-011**: A deactivated or removed employee account MUST NOT be able to log in, and MUST NOT retain access via an already-open session beyond its next request.
- **FR-012**: The system MUST NOT expose any candidate personal data (name, phone, email, CV, CTC, etc.) on the login page or the authenticated landing area introduced by this spec — no candidate database access is in scope here (constitution Principle II).
- **FR-013**: The system MUST log employee sign-in and sign-out events, consistent with the constitution's security-event logging expectation (Principle V).

### Key Entities *(include if feature involves data)*

- **Employee Account**: An internal staff member's login identity — display name, login identifier, hashed/salted password, role (Admin or Recruiter), and active/deactivated status. Holds no candidate data.
- **Role**: A fixed set of exactly two values, Admin and Recruiter, per BRD Section 6.1. Not an open-ended/growing list.
- **Session**: An employee's authenticated state after a successful login, tracked with enough recency information to enforce the 30-minute inactivity expiry, and ended by explicit sign-out, timeout, or the underlying account being deactivated.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: An employee with valid, active credentials can go from the login page to their authenticated landing area in under 10 seconds.
- **SC-002**: 100% of attempts to reach an authenticated page without a valid session are redirected to login, with zero instances of internal content appearing first.
- **SC-003**: 100% of sessions left idle for 30 minutes or more require the employee to log in again before any further action succeeds.
- **SC-004**: 100% of account-creation and account-removal attempts made by a Recruiter-role account are blocked.
- **SC-005**: A deactivated employee account fails 100% of login attempts made after deactivation.
- **SC-006**: The system is never left with zero active Admin accounts as a result of account-management actions taken through it.

## Assumptions

- This spec supersedes the placeholder built in 001-site-shell-navigation (`page-employee-login.php`), reusing its existing public URL/nav entry rather than introducing a new one.
- The employee roster is small (a handful of Admins and Recruiters), so bulk import, self-registration, and concurrent-edit conflict handling are out of scope.
- Initial passwords for new accounts are set directly by the Admin creating the account (e.g., a temporary password shared with the new hire) and can be changed by the employee once logged in; email-based "forgot password" self-service is out of scope for this spec, since no transactional-email infrastructure exists yet in this project — it can follow later without changing anything specified here.
- Two-factor authentication is out of scope for this spec; BRD Section 6.1/8 do not require it, and it can be layered on later without changing the requirements above.
- "Activity" that keeps a session alive is any request the employee makes to an authenticated page or action — no separate background/heartbeat mechanism is assumed.
- The very first Admin account (before any Admin exists to create one) is provisioned once, outside the self-service flow this spec describes — how is a planning-phase decision, not a spec-level concern.
- Candidate database access, search/filter, and CV handling (constitution Principles II-IV) are explicitly out of scope for this spec and belong to a follow-on feature that will sit behind the authentication this spec establishes.

# Feature Specification: Candidate Database, CV Upload & Review Workflow

**Feature Branch**: `012-candidate-database`

**Created**: 2026-08-21

**Status**: Draft

**Input**: User description: "Candidate Database, CV Upload & Review Workflow (Module 2 continuation, BRD Section 6.2-6.6 and 7.1): the follow-on feature explicitly deferred by 011-employee-login and by 006-for-candidates-page's FR-004. Covers: (1) an internal candidate database (20 fields per BRD 6.2), CV file upload (PDF/DOC/DOCX, max 5MB); (2) mandatory duplicate detection on phone+email at every entry point, surfacing the existing profile side-by-side on a match; (3) a candidate search/filter module with a sortable results table, view-profile, CV download, and Excel export, paginated 20/50/100 per page; (4) a public-facing CV submission form (5 fields) landing in a Pending Review queue, never written directly into the searchable database; (5) the review workflow: Approve promotes to the searchable database (status Active), Reject moves to Archived/Rejected with an optional reason code, every decision timestamped and linked to the reviewing employee; (6) employees cannot edit/delete records added by other employees (Admin-only override); (7) pending-review count shown as a badge. Access gated by 011-employee-login's existing authentication/roles. Scale target: up to 10,000 candidate profiles, search under 2 seconds. CV files must never be reachable via a public/unauthenticated URL."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Recruiter adds a candidate profile (Priority: P1)

A Recruiter or Admin fills in a new candidate's details — up to 20 fields covering identity, experience, compensation, and availability — and optionally attaches their CV, so the profile becomes part of the firm's searchable talent pool.

**Why this priority**: Nothing else in this feature has data to work with until profiles can be entered. This is the foundational capability everything else builds on.

**Independent Test**: Add one profile with a phone/email that doesn't already exist and confirm it saves; add a second with a phone number matching the first and confirm the duplicate warning appears before it saves.

**Acceptance Scenarios**:

1. **Given** a logged-in Recruiter, **When** they fill in every mandatory field (Candidate Name, Phone, Email, Total Experience, Department, CV Attachment) and submit, **Then** the profile is saved with status Active, timestamped, and recorded as added by that employee.
2. **Given** a logged-in Recruiter, **When** they submit a profile whose phone number or email matches an existing record, **Then** the system blocks saving and shows the existing profile side-by-side for comparison.
3. **Given** a logged-in Admin, **When** they add a historical profile without a CV attachment, **Then** the system allows it as a documented exception while still requiring the other mandatory fields.
4. **Given** a logged-in Recruiter, **When** they view a profile added by a different employee, **Then** they can see it but cannot edit or delete it.
5. **Given** a logged-in Admin, **When** they view any profile regardless of who added it, **Then** they can edit or delete it.

---

### User Story 2 - Recruiter searches and filters candidates (Priority: P2)

A Recruiter or Admin searches the candidate database — by name, department, experience range, location, CTC range, notice period, client name, or which employee added the profile — to quickly shortlist people for an open position.

**Why this priority**: The entire point of building the database is to make candidates findable. This is the payoff, buildable once profiles exist (User Story 1).

**Independent Test**: With a few profiles in the system, run a search with one filter and confirm only matching results appear; combine two filters and confirm results narrow correctly.

**Acceptance Scenarios**:

1. **Given** candidate profiles exist, **When** a Recruiter searches by department, **Then** only Active profiles in that department appear.
2. **Given** a result set, **When** the Recruiter applies an experience range and a location filter together, **Then** results match all applied filters at once.
3. **Given** a result list, **When** the Recruiter opens View Profile on a row, **Then** they see the full candidate record.
4. **Given** a result list, **When** the Recruiter downloads a CV from a row, **Then** the correct file downloads without ever being reachable via a public URL.
5. **Given** a result list, **When** the Recruiter exports the filtered results, **Then** a spreadsheet file downloads containing exactly the filtered/sorted rows shown.
6. **Given** more than one page of results, **When** the Recruiter changes the page size (20/50/100), **Then** results paginate accordingly.

---

### User Story 3 - Candidate submits a profile publicly, employee reviews it (Priority: P3)

A website visitor submits their basic details through a public form. The submission waits in a Pending Review queue until a Recruiter or Admin validates it, fills in the remaining internal fields, and approves it into the searchable database or rejects it.

**Why this priority**: Depends on the database existing (User Story 1) and delivers the site's public-facing candidate pipeline. Independently testable/deployable once the internal database exists, without needing search (User Story 2) built first.

**Independent Test**: Submit the public form as a visitor and confirm it does not appear in Recruiter search results yet; as a Recruiter, open the Pending Review queue, approve it, and confirm it now appears in search.

**Acceptance Scenarios**:

1. **Given** the public CV form, **When** a visitor submits Name, Phone, Email, Experience, Location, CTC, and Department, **Then** they see an on-page confirmation and the submission does NOT appear in Recruiter search results. (Phone and Email were added to the BRD's originally-listed 5 fields — see Assumptions: without them, the duplicate check this same story requires literally cannot run.)
2. **Given** a new pending submission, **When** the system checks it against the existing database, **Then** any phone/email match is flagged and shown side-by-side to the reviewing employee.
3. **Given** a pending submission with no duplicate, **When** a Recruiter opens it, fills in the remaining fields, and clicks Approve, **Then** it becomes a full candidate record with status Active, searchable immediately, timestamped and linked to that employee.
4. **Given** a pending submission, **When** a Recruiter clicks Reject and optionally selects a reason (Duplicate / Incomplete / Not Relevant / Spam), **Then** it moves to an Archived/Rejected list, is not searchable, and is retained under the existing 24-month retention policy.
5. **Given** one or more pending submissions exist, **When** any employee views the employee portal, **Then** a badge shows the current pending count.

---

### User Story 4 - Dashboard shows portal-wide counts (Priority: P4)

When a Recruiter or Admin lands on the employee portal, they see how many employee accounts exist, how many candidate profiles are Active, how many are awaiting review, and who logged in most recently — a single-glance overview.

**Why this priority**: A pure visibility layer on top of data that already exists once User Stories 1-3 (and 011-employee-login's account list) are in place. Lowest risk, ships last.

**Independent Test**: With a known number of employees, active profiles, and pending submissions, load the dashboard and confirm every count matches.

**Acceptance Scenarios**:

1. **Given** any number of employee accounts, **When** an Admin views the dashboard, **Then** the Employee Count card shows the current total of active employee accounts.
2. **Given** any number of Active candidate profiles, **When** any employee views the dashboard, **Then** the Active CVs card shows that count.
3. **Given** any number of Pending Review submissions, **When** any employee views the dashboard, **Then** the Pending Review card shows that count and matches the badge from User Story 3.
4. **Given** employee sign-in history exists, **When** any employee views the dashboard, **Then** a Recent Logins list shows the most recently signed-in employees and when they last signed in.

---

### Edge Cases

- A duplicate check matches on phone but not email, or vice versa — still counts as a match (either field is sufficient, per BRD 6.3).
- A CV file exceeds 5MB or is an unsupported format — upload is rejected with a clear message before the record is saved.
- A Recruiter tries to edit or delete a profile added by someone else — blocked; only Admin or the original adding employee may edit.
- The same person submits the public form again after an earlier submission was rejected — treated as a new submission, running the same duplicate check, which now also matches the rejected record.
- Search results exceed the chosen page size — paginated, never all loaded at once.
- A CV file is requested by a direct link without a valid session — access denied, never served without authentication.
- A candidate record reaches the 24-month retention boundary — deleted per the existing retention policy (constitution Principle VI); this feature enforces the rule already published in the Privacy Policy, it doesn't change it.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Recruiters and Admins MUST be able to add a candidate profile capturing the fields defined in BRD Section 6.2, with Candidate Name, Phone, Email, Total Experience, Department, and CV Attachment mandatory — except CV Attachment MAY be omitted by an Admin adding a historical/legacy record.
- **FR-002**: The system MUST run a duplicate check on phone number and email every time a candidate record is about to be created — both when an employee adds one directly and when a public submission is reviewed — and MUST show the existing matching profile side-by-side before the new record is finalized.
- **FR-003**: The system MUST record, for every candidate profile, which employee added it and when, automatically, without manual entry of those two fields.
- **FR-004**: An employee MUST NOT be able to edit or delete a candidate profile added by a different employee; an Admin MUST be able to edit or delete any profile.
- **FR-005**: Recruiters and Admins MUST be able to search/filter candidate profiles by name, department, experience range, location, CTC range, notice period, client name, and which employee added the profile, with results narrowing when multiple filters are combined.
- **FR-006**: Search results MUST display as a sortable table (at minimum Name, Experience, Location, CTC) with a way to open the full profile, download the attached CV, and export the current filtered/sorted results to a spreadsheet file.
- **FR-007**: Search results MUST be paginated, with a choice of 20, 50, or 100 results per page.
- **FR-008**: The public website MUST provide a CV submission form capturing Name, Phone, Email, Experience, Location, CTC, and Department, requiring no account or login from the visitor. (Phone and Email are not in the BRD's own "5 basic fields" summary, but BRD Section 6.6 step 3 requires the duplicate check to run against phone/email for every submission — with neither field captured, that check has nothing to match on. Since duplicate detection is NON-NEGOTIABLE at every entry point (constitution Principle IV, FR-002), the two fields were added rather than shipping a public entry point structurally exempt from the constitution's own hard requirement.)
- **FR-009**: A public submission MUST be stored in a Pending Review state and MUST NOT appear in Recruiter/Admin search results until an employee approves it.
- **FR-010**: An employee reviewing a pending submission MUST be able to fill in the remaining candidate-database fields before deciding to Approve or Reject it.
- **FR-011**: Approving a pending submission MUST move it into the searchable candidate database with status Active, timestamped and linked to the approving employee.
- **FR-012**: Rejecting a pending submission MUST move it to an Archived/Rejected state (not searchable), optionally recording a reason (Duplicate, Incomplete, Not Relevant, or Spam), timestamped and linked to the rejecting employee.
- **FR-013**: The employee portal MUST display a count of currently pending submissions, visible without having to open the review queue first.
- **FR-014**: The employee portal's dashboard MUST show, at minimum: total active employee accounts, total Active candidate profiles, total Pending Review submissions, and a list of the most recently signed-in employees.
- **FR-015**: CV files MUST NOT be reachable by any unauthenticated request or public URL; every access MUST go through the same authentication and role checks as the rest of the employee portal.
- **FR-016**: A public submission MUST NOT trigger any email or other communication back to the candidate; the visitor sees only an on-page confirmation that their submission was received.
- **FR-017**: Archived/Rejected candidate records MUST be retained and deleted under the same 24-month-from-last-activity policy already established for Active records (constitution Principle VI), not exempted from it.

### Key Entities

- **Candidate Profile**: The 20-field internal record (BRD 6.2) — identity/contact fields, role/experience fields, compensation/availability fields, the CV file, and provenance (who added it, when, source). Has a status: Active, Pending Review, or Archived/Rejected.
- **Pending Submission**: A public visitor's 7-field entry (Name, Phone, Email, Experience, Location, CTC, Department — see FR-008's note) — effectively a Candidate Profile in the Pending Review status, missing most of the 20 fields until an employee fills them in during review.
- **Review Decision**: The record of an Approve or Reject action — which employee, when, and (for rejections) an optional reason code.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A Recruiter can add a new, non-duplicate candidate profile with CV in under 3 minutes.
- **SC-002**: 100% of new candidate records — whether added directly or approved from a public submission — that share a phone number or email with an existing record are flagged before being finalized.
- **SC-003**: A filtered search across up to 10,000 candidate profiles returns results in under 2 seconds.
- **SC-004**: 0% of public CV submissions ever appear in search results before an employee has explicitly approved them.
- **SC-005**: 100% of CV file access attempts without a valid employee session are denied.
- **SC-006**: An employee can find and open the profile of a specific candidate they're looking for using at least one filter, without scrolling through unrelated results.
- **SC-007**: The dashboard's counts (employees, active profiles, pending review) match the underlying data with zero discrepancy at any point in time.

## Assumptions

- **Correction found during implementation (2026-08-21)**: BRD Section 7.1/6.6 describe the public form as capturing "5 basic fields" — Name, Experience, Location, CTC, Department — but BRD 6.6 step 3 requires running "the same phone/email logic as Section 6.3" duplicate check against every submission. Those two statements are inconsistent with each other: a submission with neither phone nor email has nothing for that check to match on. Since duplicate detection is NON-NEGOTIABLE at every entry point (constitution Principle IV) and not optional, Phone and Email were added to the public form (FR-008) rather than shipping a submission path exempt from a hard constitutional requirement. This affects User Story 3/AS1, FR-008, and the Pending Submission entity above; data-model.md already reflected the 7-field version.
- This feature builds entirely on the authentication, roles, and session handling already established in 011-employee-login; it does not modify login, accounts, or session behavior.
- The public CV submission form is placed on the existing For Candidates page (006-for-candidates-page), replacing that page's current "Contact Us" call-to-action for profile submission — 006's FR-004 explicitly deferred this form to "a separate later module," which is this one.
- "Export to a spreadsheet file" means a widely-compatible format (e.g. CSV or XLSX); the exact format is a planning-phase decision, not a spec-level requirement.
- CV file storage location/mechanism (server-local vs. cloud storage) is a planning-phase decision; the only spec-level requirement is that it is never publicly reachable (FR-015).
- "Most recently signed-in employees" on the dashboard reuses the sign-in event logging already built in 011-employee-login rather than introducing a second logging mechanism.
- Duplicate detection matches on phone number OR email — either one matching alone is sufficient to flag a record, per BRD 6.3.
- No candidate-facing account or login exists; a candidate cannot check their own submission's status. This matches BRD 7.1 (the public form is anonymous) and isn't contradicted elsewhere in the BRD.

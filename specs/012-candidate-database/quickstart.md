# Quickstart: Validating the Candidate Database, CV Upload & Review Workflow

This is a validation/run guide, not an implementation guide. It proves the
feature satisfies spec.md end-to-end. Full implementation steps belong in
tasks.md.

## Prerequisites

- `eminence-portal` (011-employee-login) active, with at least one Recruiter
  and one Admin test account (see 011's quickstart.md for how to create
  them).
- This feature's table created (`wp db query "SHOW TABLES LIKE
  '%eminence_candidates%'"` — or the SQLite-compatible equivalent used by
  this project's local preview).
- The For Candidates page has `[eminence_cv_submission]` in its content
  (research.md #8) — confirm with `wp post content-get <id>` before testing
  User Story 3.

## Validation scenarios (map to spec.md Acceptance Scenarios)

| # | Story | Action | Expected Result |
|---|---|---|---|
| 1 | US1 / AS1 | As a Recruiter, submit the add-candidate form with every mandatory field and a CV file | Record saved, status `active`, `added_by_user_id` = that Recruiter, `date_added` set automatically |
| 2 | US1 / AS2 | Submit a second profile whose phone matches Scenario 1's | Blocked before saving; existing profile shown side-by-side |
| 3 | US1 / AS3 | As an Admin, submit a profile with "historical record" checked and no CV | Saved without a CV; other mandatory fields still required |
| 4 | US1 / AS4 | As a different Recruiter, open Scenario 1's record | Visible; Edit/Delete controls absent or disabled |
| 5 | US1 / AS5 | As an Admin, open Scenario 1's record | Edit/Delete available regardless of who added it |
| 6 | US2 / AS1 | Search by department only | Only `active` profiles in that department returned |
| 7 | US2 / AS2 | Add an experience-range filter and a location filter together | Results satisfy both filters at once |
| 8 | US2 / AS3 | Click View Profile on a result row | Full record displayed |
| 9 | US2 / AS4 | Click Download CV on a result row | Correct file downloads; response never a direct uploads-folder URL |
| 10 | US2 / AS5 | Export the current filtered results | CSV downloads, rows match exactly what's on screen |
| 11 | US2 / AS6 | Change page size between 20/50/100 | Result count per page changes accordingly |
| 12 | US3 / AS1 | As an anonymous visitor, submit the public form (Name, Phone, Email, Experience, Location, CTC, Department — see spec.md Assumptions on the Phone/Email correction) | On-page confirmation shown; record created with status `pending_review`; does NOT appear in Scenario 6-11 search results |
| 13 | US3 / AS2 | Submit the public form again with the same phone/email as an existing record | Duplicate flagged for the reviewing employee, existing profile shown side-by-side |
| 14 | US3 / AS3 | As a Recruiter, open the Scenario 12 submission, fill remaining fields, Approve | Status becomes `active`, `reviewed_by`/`reviewed_at` set, now appears in search |
| 15 | US3 / AS4 | Submit another public entry, then Reject it with reason "Spam" | Status becomes `archived_rejected`, `reject_reason` = Spam, does not appear in search |
| 16 | US3 / AS5 | With at least one pending submission, view the portal | Pending-count badge shows the correct number |
| 17 | US4 / AS1 | As an Admin, sign in | Lands directly on the Dashboard; Employee Count card matches `wp user list --role__in=eminence_recruiter,eminence_portal_admin \| wc -l` |
| 18 | US4 / AS2 | View the Dashboard | Active CVs card matches a direct count of `status = 'active'` rows |
| 19 | US4 / AS3 | View the Dashboard | Pending Review card matches Scenario 16's badge exactly |
| 20 | US4 / AS4 | View the Dashboard | Recent Logins list reflects 011's sign-in log, most recent first |

## Automated / scripted checks

- **Duplicate-detection sweep** (supports SC-002): script both entry points
  (direct add, review-approval) against the same set of phone/email
  collisions and confirm both are caught by the one shared function
  (research.md #5) — not just one of the two paths.
- **CV access-denial sweep** (supports SC-005): `curl` the download endpoint
  with no session, with a Recruiter session, and with a guessed/incorrect
  candidate ID; only a valid session + valid ID succeeds.
- **Public-submission isolation check** (supports SC-004): immediately after
  Scenario 12, run every search filter combination from Scenarios 6-11 and
  confirm the pending record never appears in any of them.
- **Scale/performance check** (supports SC-003): seed ~10,000 synthetic
  candidate rows (a small WP-CLI or PHP script generating randomized
  values across the indexed columns), then time a filtered search combining
  at least three fields (e.g. department + experience range + location) —
  must return in under 2 seconds. Re-run with a different filter
  combination to avoid measuring one lucky/cached query plan.
- **Retention check** (supports FR-017): create one `active` and one
  `archived_rejected` record with `last_activity_at` set to more than 24
  months ago (`wp db query` / direct update), run whatever
  retention-sweep mechanism the implementation lands on, and confirm both
  are deleted — not just the `active` one.

## Cleanup

```
wp db query "DELETE FROM {prefix}eminence_candidates WHERE candidate_name LIKE 'Test %'"
```
(adjust the `WHERE` to match whatever naming convention the test data used —
the synthetic 10,000-row seed in particular should be fully removed, not
left sitting in the local preview database.)

# Contract: Candidate data access points

This feature has no public API. It has three contracts: the CV download
endpoint's interface, what future portal features can rely on from
`candidate-repository.php` without re-implementing it, and the boundary this
feature will NOT cross.

## CV download endpoint

- One entry point, `cv-storage.php`, reached via a query var (e.g.
  `?eminence_cv_download={candidate_id}`), never a direct file URL.
- Every request MUST pass `current_user_can( 'eminence_manage_candidates' )`
  and a nonce check before the file path is even looked up — a failed check
  returns a 403/`wp_die()`, the same "deny before you even know if the
  record exists" posture 011-employee-login's account-management screen
  already established.
- The response streams the file with a `Content-Disposition: attachment`
  header (forces download, doesn't render inline) and the candidate's own
  name in the filename, not the server's internal storage path.

## What future portal features can rely on

Any later feature (e.g. the BRD's Phase-2 ATS/client-portal items, or a
"my open requirements" view for employers) can use, without redefining:

1. **`eminence_find_duplicate_candidate( $phone, $email )`** — the one
   canonical duplicate check (research.md #5). A future feature that creates
   candidate records from a new entry point (e.g. a bulk import) MUST call
   this, not write its own phone/email comparison.
2. **`status = 'active'` is the entire definition of "searchable"** — any
   future feature that needs "real" candidates (not pending, not rejected)
   filters on this one column. No second "is this candidate real" flag
   should ever be introduced.
3. **`eminence_manage_candidates` is the one capability gating all candidate
   data** — a future feature narrowing access further (e.g. "only see
   candidates you added") adds its own additional check on top of this one,
   it does not replace it or introduce a competing capability for the same
   question this one already answers.
4. **The CV download endpoint is the only sanctioned way to read a CV file**
   — a future feature must not construct its own path to
   `cv-storage.php`'s private upload directory; it calls the same endpoint.

## What this feature does NOT provide

- No candidate-facing account, login, or status-check capability (spec
  Assumptions) — a future "check your application status" feature is
  out of scope here and would need its own spec.
- No client/employer-facing visibility into the candidate database at all —
  BRD Section 4.2 marks a client portal as Phase 2, and nothing in this
  feature exposes candidate data to anyone outside the two employee roles.
- No bulk import/export beyond the one-way CSV *export* in FR-006 — there is
  no bulk *upload* of many candidates at once; User Story 1 is one profile
  at a time, matching BRD 6.2's own framing as a manual-entry form.

## Stability

This contract is considered stable once 012-candidate-database reaches
"Implemented" status. A change to the duplicate-check function's signature,
the `status` values, the `eminence_manage_candidates` capability, or the CV
download endpoint's URL scheme after that point requires re-checking every
feature built on top of it.

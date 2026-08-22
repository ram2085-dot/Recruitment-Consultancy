# Phase 0 Research: Candidate Database, CV Upload & Review Workflow

## 1. Storage model: custom table vs. custom post type

**Decision**: A single custom database table, `{$wpdb->prefix}eminence_candidates`,
with a real, indexed column per BRD-6.2 field — not a custom post type (CPT)
with the fields stored as postmeta.

**Rationale**: SC-003 requires a search combining several filters (department,
experience range, location, CTC range, notice period, client name, added-by)
to return in under 2 seconds at up to 10,000 rows (BRD Section 8). WordPress's
native `WP_Query` `meta_query` implements this as a self-join against
`wp_postmeta` per filtered field — postmeta is an EAV (entity-attribute-value)
table with no per-field type or index, and range filters (experience, CTC) on
it are a well-known WordPress performance trap even at moderate scale. A
purpose-built table with real column types and indexes on every
filterable/lookup field (phone, email, department, location, experience,
CTC, notice period, client name, added-by, status) is the only way to make
that guarantee dependable rather than hopeful.

**Alternatives considered**: CPT + postmeta — rejected for the performance
reason above; it's the more "WordPress-native" choice and was the default
this project reached for everywhere else (Pages, nav menus, even employee
accounts as WP users in 011), but this is the first feature whose own
success criteria specifically demand indexed multi-field range queries at
five-figure scale, which is exactly the case that default doesn't handle
well. A fully separate custom plugin table *per* status (three tables) —
rejected, see #2.

## 2. One table with a status column, not three tables

**Decision**: `active`, `pending_review`, and `archived_rejected` are values
of a single `status` column on the one candidates table, not three separate
tables.

**Rationale**: Approve/Reject (FR-011/FR-012) become a single `UPDATE ...
SET status = ?` plus a few review-metadata columns — no data migration
between tables, no risk of a record existing in two places at once, and
every search query is just "the same table, filtered to `status = 'active'`"
(directly satisfies FR-009 by construction: there is no `active`-table
INSERT path that a public submission could ever reach).

**Alternatives considered**: Separate `pending_submissions` table promoted
into the candidates table on Approve — rejected; it would require copying
every field across on approval, duplicate schema definitions to keep in
sync, and a two-step "does this candidate exist in table A or table B"
check anywhere the app needs to know about a person, none of which this
feature actually needs.

## 3. CV file storage and access

**Decision**: CV files upload into a dedicated, non-guessable subdirectory
under `wp-content/uploads/` (e.g. `eminence-cv-private/{random-token}/`)
rather than a normal media-library path. No UI anywhere links to that path
directly. Every download — from search results, from a candidate's full
profile, from the review queue — goes through one capability-gated PHP
endpoint (`cv-storage.php`) that checks `current_user_can(
'eminence_manage_candidates' )`, looks up the real file path server-side,
and streams it; a request without a valid session gets nothing, regardless
of whether it guesses a URL.

**Rationale**: FR-015 and constitution Principle II are explicit: CV files
must never be reachable by an unauthenticated request. Relying solely on an
`.htaccess`/web-server rule to block the upload directory is not dependable
across hosting environments (Nginx doesn't read `.htaccess`; the project's
own constitution doesn't fix a specific web server) — the PHP-level
capability check is the actual, portable guarantee; directory obscurity is
only a defense-in-depth bonus on top of it, never the sole mechanism.

**Alternatives considered**: Cloud storage (AWS S3), which the BRD lists as
an option — rejected for now per Principle I (no new external service/
dependency without justification) and because local/server storage
comfortably handles the target scale (10,000 profiles × up to 5MB ≈ 50GB
ceiling, well within typical hosting). Storing CVs as WordPress media-library
attachments — rejected, the media library's default URLs are public by
design and making them private would mean fighting WordPress core's own
attachment-URL behavior instead of just not using it.

## 4. Export format: CSV, not a spreadsheet library

**Decision**: "Export filtered results" (FR-006) generates a CSV file via
PHP's native `fputcsv()`.

**Rationale**: CSV opens natively in Excel/Sheets — satisfying the BRD's
"export to Excel" intent — without adding a spreadsheet-generation library
(e.g. PhpSpreadsheet) and the Composer dependency-management setup this
project has never needed until now. Zero new dependencies, matching
Principle I and the project's consistent bias (cookie consent, this plugin's
own auth) toward small owned code over an added library for a narrowly-
scoped need.

**Alternatives considered**: A real `.xlsx` via a library — rejected for the
dependency-weight reason above; nothing in the spec requires native Excel
formatting (formulas, multiple sheets, styling) that CSV can't represent.

## 5. Duplicate detection: one shared function, both entry points

**Decision**: A single `eminence_find_duplicate_candidate( $phone, $email )`
function in `candidate-repository.php`, called from both the direct-add form
(User Story 1) and the review-approval step (User Story 3). It matches on
phone OR email (either alone is sufficient — BRD 6.3) against candidates in
*any* status, including `archived_rejected`.

**Rationale**: Principle IV is explicit that duplicate detection is required
at both entry points; routing both through the same function is what makes
it structurally impossible for the two paths to drift out of sync with each
other (one check to test, one check to get right). Including
`archived_rejected` records in the match is what makes the spec's own edge
case true — a previously-rejected person re-submitting should surface that
history to the reviewing employee, not silently look like a first-time
submission.

**Alternatives considered**: Separate duplicate-check implementations for
the internal-add path and the review path — rejected, this is exactly the
kind of duplication Principle IV's rationale calls out as reintroducing the
problem it exists to prevent.

## 6. Capabilities: extend the existing two roles, no new role

**Decision**: Two new capabilities, added to both `eminence_recruiter` and
`eminence_portal_admin` from 011-employee-login:
`eminence_manage_candidates` (add, search, view, review) and, Admin-only,
`eminence_edit_any_candidate` (edit/delete a record added by someone else).

**Rationale**: BRD 6.2-6.6 never restricts adding, searching, or reviewing to
Admins only — "Recruiter / Admin" is the actor for every one of those steps.
The one real permission split in the BRD is FR-004's ownership rule (only
Admin can touch another employee's record), which maps to exactly one new
capability rather than a new role. This keeps the role model 011 already
established (and the `portal-auth-contract.md` promise that a future feature
would define its own capabilities rather than overload
`eminence_manage_employees`) intact.

**Alternatives considered**: A third role for some kind of "senior recruiter"
tier — nothing in the BRD supports it; would be inventing scope.

## 7. Admin menu becomes a real group; Admin's post-login redirect changes

**Decision**: `account-management.php`'s `admin_menu` registration changes
from one top-level page to a menu group: **Dashboard** (new, default),
**Employee Accounts** (moved from top-level, unchanged behavior), **Candidates**
(search/filter), **Add Candidate**, **Pending Review** — all still gated on
the same capabilities as before (Employee Accounts stays
`eminence_manage_employees`-only; the four candidate-related items require
`eminence_manage_candidates`, so a Recruiter sees Dashboard/Candidates/Add
Candidate/Pending Review but not Employee Accounts). `auth.php`'s
post-login redirect for a Portal Admin changes from going straight to
Employee Accounts to going to the new Dashboard instead.

**Rationale**: This is the direct continuation of the user's own prior
request ("admin shall land on his dashboard") — at the time there was no
real dashboard to land on, so Employee Accounts was the closest stand-in;
now there is one, so the redirect target is corrected to match what was
actually asked for. Restructuring one `admin_menu` call and one redirect
target is a two-line-of-intent change, not a re-opening of 011's actual
login/session/account logic.

**Alternatives considered**: A separate top-level "Employee Portal" menu
with everything (including Employee Accounts) as children from the start —
effectively what this decision produces anyway; no meaningful alternative
here beyond confirming the grouping.

## 8. Public form placement: shortcode + content edit, not a new template

**Decision**: `[eminence_cv_submission]` is a shortcode (`public-cv-form.php`),
inserted into the existing For Candidates page's WordPress-editor content —
the same generic `page-with-hero.php` template 006-for-candidates-page
already uses, unchanged. Placing the shortcode on the page is a content
edit (scripted via WP-CLI in quickstart.md/tasks.md), not a template code
change.

**Rationale**: Unlike 011's login shortcode (which needed to fully replace a
page's content conditionally) or the testimonials page (which needed CPT
query logic no generic template has), this form is fully self-contained —
it doesn't need anything from the page template beyond "render me somewhere
in the content," which is exactly what a shortcode in the editor is for.
This also keeps the placement business-owner-editable (movable within the
page, or onto a different page later) without a deployment, consistent with
this project's whole approach to page content.

**Alternatives considered**: A dedicated `page-for-candidates.php` template
hardcoding the form's position — rejected, adds a template file for no
reason when the shortcode approach already satisfies FR-008 and matches how
page content has worked everywhere else in this project.

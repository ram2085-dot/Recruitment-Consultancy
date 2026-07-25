<!--
Sync Impact Report
- Version change: TEMPLATE → 1.0.0 (initial ratification)
- Modified principles: n/a (first fill of template placeholders)
- Added sections: Core Principles I–VII, Technical Constraints, Development Workflow, Governance
- Removed sections: none
- Templates requiring updates:
  - .specify/templates/plan-template.md ⚠ pending (review Constitution Check gate against these principles before first /speckit-plan run)
  - .specify/templates/spec-template.md ⚠ pending (confirm mandatory sections cover data-separation & PII scope)
  - .specify/templates/tasks-template.md ⚠ pending (confirm task categories can express security/duplicate-check/perf gates)
  - .claude/skills/speckit-*/SKILL.md ✅ no agent-specific renames needed (generic references only)
- Follow-up TODOs: none — all placeholders resolved from BRD (Recruitment_Website_BRD_v3.docx) and user-supplied principles
-->

# Eminence Consultant Website Constitution

## Core Principles

### I. Fixed Technology Stack
The project MUST be built on WordPress (core) + a custom plugin (PHP) for the employee
portal and candidate-data features + MySQL for persistence. No alternative framework,
CMS, or database engine may be introduced without an explicit constitution amendment
recorded here. Rationale: the BRD (Section 11) fixes this stack as a business decision;
silent drift toward a different stack would invalidate hosting, CMS, and content-editing
assumptions the business owner is relying on.

### II. Role-Gated Access to Candidate Data (NON-NEGOTIABLE)
Every candidate record, CV file, and search/filter capability MUST be reachable only
through authenticated, role-based access (Admin vs. Recruiter, per BRD Section 6.1).
No candidate PII (name, phone, email, CV, CTC, etc.) may ever be exposed on a
public-facing page, public API endpoint, or unauthenticated route. Rationale: candidate
data is sensitive personal information about third parties who never agreed to public
exposure; this is both a trust and legal-liability boundary.

### III. Public/Internal Data Separation via Review Workflow
The public CV submission form (5 fields) and the internal candidate database (20 fields,
BRD Section 6.2) are distinct data models. A public submission MUST land in the Pending
Review queue (BRD Section 6.6) and MUST NOT be written directly into the searchable
candidate database. Only an explicit Approve action by a Recruiter/Admin promotes a
submission into the searchable database. Rationale: prevents unvalidated, incomplete, or
spam submissions from polluting recruiter search results.

### IV. Mandatory Duplicate Detection
Every entry point that creates a candidate record — internal CV upload (Section 6.2) and
public submission review (Section 6.6) — MUST run a duplicate check on phone number and
email before the record is finalized, and MUST surface the existing profile side-by-side
when a match is found. Rationale: the BRD explicitly requires this at both entry points;
skipping it at either one reintroduces the duplicate/spam problem Principle III exists to
prevent.

### V. Security Baseline
The following are non-negotiable minimums for every environment (dev, staging, prod):
HTTPS/SSL everywhere; credentials stored hashed and salted, never in plaintext; employee
sessions auto-expire after 30 minutes of inactivity; only Admin accounts may create or
remove employee accounts; no direct public access to the database layer. Rationale: BRD
Section 8 (Non-Functional Requirements) and Section 6.1 set these as hard requirements,
not aspirations.

### VI. Performance and Scale Targets Are Binding
Public pages MUST load in under 3 seconds; employee portal search MUST return results in
under 2 seconds; the system MUST remain performant with up to 10,000 candidate profiles
in Phase 1 (BRD Section 8). Any design or query approach that cannot demonstrably meet
these targets at that scale MUST be reworked before merge, not deferred as "optimize
later."

### VII. Phase Discipline — No Scope Creep
The following are explicitly OUT of scope for this phase and MUST NOT be implemented,
even partially, without a constitution amendment: full Applicant Tracking System (ATS),
client/employer login portal, CRM integration, interview scheduling/calendar
integration, paid advertising integration, and a mobile app (BRD Section 4.2).
Rationale: these are explicitly deferred to Phase 2 in the BRD; building toward them now
wastes budget and risks scope creep the business owner has not approved.

## Technical Constraints

- Platform: WordPress + a purpose-built custom plugin encapsulating the employee portal,
  candidate database, search/filter module, and review-workflow logic.
- Database: MySQL (or PostgreSQL only if a documented technical blocker makes MySQL
  infeasible — must be justified in the relevant /speckit-plan Constitution Check).
- File storage: CV attachments (PDF/DOC/DOCX, max 5MB) stored in secure server storage or
  cloud object storage (e.g. AWS S3); never stored with public read access.
- Hosting: reliable hosting (Indian or global) capable of sustaining 99.9% uptime.
- Analytics/SEO: Google Analytics 4 and basic on-page SEO (meta titles, descriptions, alt
  tags, sitemap) are required on all public pages.
- Browser support: latest versions of Chrome, Firefox, Safari, Edge.
- Accessibility: basic WCAG compliance (readable fonts, alt text on images) is required,
  not optional polish.

## Development Workflow

- This project follows spec-driven development: every feature proceeds through
  `/speckit-specify` → (optional `/speckit-clarify`) → `/speckit-plan` →
  `/speckit-tasks` → (optional `/speckit-analyze`) → `/speckit-implement`. Code is not
  written ahead of an approved spec and plan for that feature.
- Every `/speckit-plan` MUST include a Constitution Check that explicitly verifies the
  feature against Principles I–VII before implementation begins.
- Features touching candidate data (upload, search, review/approval) MUST include a
  verification step for Principles II, III, and IV in their spec's acceptance criteria —
  these are not implicit, they must be testable.
- Non-functional requirements (Principles V and VI) MUST be included as explicit
  acceptance criteria for any feature they apply to, not left to a final "performance
  pass."

## Governance

This constitution supersedes ad-hoc decisions and prior informal agreements about how
the site is built. Any conflict between this document and the BRD must be resolved by
amending this constitution to reflect the resolution, not by silently deviating from
either document.

Amendment procedure: propose the change, update this file via `/speckit-constitution`,
bump the version per semantic versioning (MAJOR = principle removed/redefined
incompatibly, MINOR = principle or section added, PATCH = clarification/wording), and
record the change in the Sync Impact Report at the top of this file. Dependent templates
(plan/spec/tasks) must be reviewed for alignment as part of the same amendment.

Every `/speckit-plan` and `/speckit-implement` pass MUST verify compliance with this
constitution. Complexity or deviation from a principle must be explicitly justified in
the plan's Constitution Check section, not silently introduced.

**Version**: 1.0.0 | **Ratified**: 2026-07-25 | **Last Amended**: 2026-07-25

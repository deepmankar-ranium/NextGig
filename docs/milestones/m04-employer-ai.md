# M4 — Employer AI: JD writer + ranked applicants

**Goal:** save employers time, with explainable suggestions a human stays in charge of.

## Scope
- **Job-description writer:** from a title and a few bullet points, draft a structured JD
  (responsibilities, requirements, skills as tags). The employer edits before publishing.
- **Ranked applicants:** the applicant list for a job can be sorted by match score (from M3), each
  applicant with a "why" panel.
- **Human-in-the-loop by design:**
  - There is no auto-reject; every status change is a human action.
  - The ranking ignores name, photo, gender, age and location. These are never sent to the AI.
  - The employer can switch the ranking off and see the plain list.
- **Audit trail:** a log of who changed which application status and when.

## Why this matters
Hiring is a regulated AI use. The EU AI Act classes it as high-risk, and New York City's Local
Law 144 requires bias audits for automated hiring tools. Designing for this shows real-world
judgement. Say so in the README.

## Out of scope
Automated emails to candidates. Interview scheduling.

## Acceptance criteria
- [x] A JD draft is never published without an explicit employer action.
- [x] Prompts contain no protected attributes; a test asserts this.
- [x] Rankings are visible only to the employer who owns the job.
- [x] No code path changes application status without a human request.
- [x] Every status change is recorded with the actor and a timestamp.

## Tests
- Prompt-content test: name, email and gender are absent from what's sent to the provider.
- Ownership: employer B cannot see employer A's rankings.
- Audit entry written on each status change.

## Decisions (2026-11-17)
1. **Yes, transparency notice** on the apply form: scores use skills and experience only, and a
   person makes every decision.

## Notes
Branch `feature/m04-employer-ai`.

- **JD writer**: `POST /Jobs/draft` (employers, `throttle:ai`) → `JobDescriptionWriter`. Input is
  only the title, notes and min years. Returns description text, skills, matching `tag_ids` and
  `new_skills` (skills with no tag yet). It fills the create form; it never saves a job.
- **Ranked applicants**: `/view-applications` sorts by the M3 match score (code, no AI call) with a
  "why?" row: matched / missing skills and the status history. `?ranked=0` (button) shows the
  plain newest-first list. Inputs are skills and years only, so protected attributes can't
  influence the order. Seekers' `resume_text` / `profile` stay hidden from the payload.
- **Audit trail**: `application_status_history` (append-only model `ApplicationStatusChange`;
  updates/deletes throw). Every change goes through `Application::transitionTo($status, $actor)`,
  used by apply and by the employer's status update. M5 builds the timeline on this table.
- Fixed: new applications were saved as `'Pending'` (capital P), not a valid enum value.


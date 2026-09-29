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
- [ ] A JD draft is never published without an explicit employer action.
- [ ] Prompts contain no protected attributes; a test asserts this.
- [ ] Rankings are visible only to the employer who owns the job.
- [ ] No code path changes application status without a human request.
- [ ] Every status change is recorded with the actor and a timestamp.

## Tests
- Prompt-content test: name, email and gender are absent from what's sent to the provider.
- Ownership: employer B cannot see employer A's rankings.
- Audit entry written on each status change.

## Unresolved questions
1. Show seekers that AI ranking is used (a transparency notice)? Recommended: yes.

## Notes

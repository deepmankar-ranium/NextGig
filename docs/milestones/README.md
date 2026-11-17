# NextGig Milestones

**Goal (2026-09-25):** turn NextGig from "a job board" into **a real-time hiring platform with
explainable AI matching**. It's a secondary portfolio project, so the budget is about 1–2 weeks.
Don't let it compete with Chattrix for time.

One file per milestone. Each is a ship-gate: finish it before starting the next.

| # | Milestone | Status | Depends on |
| --- | --- | --- | --- |
| [M1](m01-security-fixes.md) | Security fixes + tests | ☑ (PR pending) | — |
| [M2](m02-ai-foundation.md) | AI foundation (replace generic chatbot) | ☑ (PR pending) | M1 |
| [M3](m03-explainable-job-match.md) | Explainable job match (seekers) | ☑ (PR pending) | M2 |
| [M4](m04-employer-ai.md) | Employer AI: JD writer + ranked applicants | ☑ (PR pending) | M3 |
| [M5](m05-realtime-application-timeline.md) | Real-time application timeline | ☑ (PR pending) | M1 |
| [M6](m06-polish-deploy.md) | Polish + deploy | ◐ polish done, deploy deferred (PR pending) | M1–M5 |

| M7 | UI/UX pass (nav, dashboard, tabs, mobile) | ☑ (PR pending) | M6 |

**Stand-out threshold:** M1 + M3 + M6. M4 and M5 are strong extras.

## Rules for every milestone

- One branch and one PR per milestone. Never commit to `main` directly.
- Every fix and feature ships with feature tests. The only tests today are the Breeze auth
  defaults.
- Authorization lives in policies or Form Request `authorize()`. Checking the role alone is not
  ownership.
- **AI never makes the hiring decision.** It explains and suggests; a human decides.
- No resume text or AI prompts in logs. Log ids and error codes only.
- When a milestone is done, update its status box above and add to its *Notes*.

## Pitch after M6

> Real-time hiring platform: seekers see *why* they match a job and what's missing; employers get
> an explainable, human-in-the-loop ranked shortlist and live application timelines.

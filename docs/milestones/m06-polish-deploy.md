# M6 — Polish + deploy

**Goal:** a live URL and a README that sell NextGig in two minutes.

## Scope
- Route cleanup: lowercase, consistent names (`/Home` → `/home`, `/Jobs` → `/jobs`), with
  redirects from the old paths.
- Remove leftovers: unused imports in `routes/web.php` (`ApplicationController`,
  `sendResetPasswordLink`), `Register2.vue` naming, stray debug code.
- Deploy: app, queue worker and Pusher/Reverb for real time. Seed demo data.
- Demo accounts: one job seeker and one employer, credentials in the README.
- README rewrite:
  - pitch, screenshots and a GIF of the live timeline
  - the match-score formula
  - the human-in-the-loop design and the M1 security story
  - fix the "IertiaJs" typo
- A 2-minute demo video.
- Clean up the duplicate folders in `~/Herd` (` NextGig`, ` NextGig123`) once you've decided what
  to keep.

## Out of scope
New features.

## Acceptance criteria
- [ ] Live URL works end to end for both demo roles.
- [ ] Demo data is reset on a schedule, or demo accounts are limited, so the demo can't be
      vandalised.
- [ ] No secrets in the repo; `.env` is not committed.
- [ ] CI runs the test suite on every PR.
- [ ] The README leads with the pitch and the video.

## Tests
- A smoke test against the deployed URL: log in as a seeker, open a job, see the match panel.

## Unresolved questions
1. Hosting: Laravel Cloud, Railway, or a VPS?
2. Keep MySQL, or move to Postgres to match Chattrix?

## Notes

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
- [x] Demo data is reset on a schedule, or demo accounts are limited, so the demo can't be
      vandalised.
- [x] No secrets in the repo; `.env` is not committed.
- [x] CI runs the test suite on every PR.
- [ ] The README leads with the pitch and the video.

## Tests
- A smoke test against the deployed URL: log in as a seeker, open a job, see the match panel.

## Unresolved questions
1. Hosting: Laravel Cloud, Railway, or a VPS?
2. Keep MySQL, or move to Postgres to match Chattrix?

## Notes
Branch `feature/m06-polish` (2026-11-17). **Deployment deferred** at the owner's request: live URL,
hosting choice, Pusher/Reverb in production, smoke test against the deployed URL, screenshots,
GIF and demo video are still to do.

Done:
- Routes lowercased (`/home`, `/jobs`, `/jobs/{id}`, `/jobs/{id}/edit`, `/jobs/create`) with 301
  redirects from the old capitalised URLs; job ids constrained to numbers.
- Leftovers: unused route imports removed (M1), `Register2.vue` → `RegisterDetails.vue`, all
  `console.log` and the debug `Log::info` removed.
- **Linux fix:** `App\Models\Jobseeker` lived in `JobSeeker.php` (and its factory likewise), which
  only autoloads on case-insensitive filesystems. Renamed; every class now matches its file name.
  `DatabaseSeeder` referenced `JobseekerSeeder` (wrong case) — fixed.
- `DemoSeeder` + `php artisan demo:reset` (only with `DEMO_MODE=true` or in local/testing);
  nightly reset scheduled when `DEMO_MODE=true`. Demo accounts in the README.
- CI: `.github/workflows/tests.yml` (PHP 8.3 tests + frontend production build).
- README rewritten: pitch, formula, human-in-the-loop, security story; "IertiaJs" typo gone.
- `.env` is not tracked (checked).

Not done / follow-ups:
- `composer audit` reports 66 advisories in existing dependencies; run `composer update` and
  re-test before deploying.
- `config/app.php` still has a Laravel 10-style `providers` list, so `RouteServiceProvider` loads
  `routes/web.php` a second time on top of `bootstrap/app.php`. Harmless today; clean up when
  convenient.
- Duplicate `~/Herd` folders (` NextGig`, ` NextGig123`) left alone: they're outside the repo.


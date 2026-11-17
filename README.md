# NextGig

**A real-time hiring platform with explainable AI matching.** Job seekers see *why* they match a
job and what's missing. Employers get a ranked, explainable shortlist and live application
timelines. A person makes every hiring decision.

Built with Laravel 12, Inertia.js + Vue 3, Tailwind CSS, MySQL, Pusher (Laravel Echo) and Google
Gemini.

## Features

**For job seekers**
- **Resume → profile.** Upload a PDF; skills, years of experience and roles are extracted and shown
  to you to review and edit before they're used anywhere.
- **Match score on every job** (0–100) with the skills you have, the ones you're missing, and an
  on-demand AI explanation with 2–3 concrete tips. "Best matches for you" sorts the job list.
- **Live application timeline.** Applied → Viewed → Shortlisted → Interview → Offer. Updates arrive
  in real time (toast + badge), and important stages also send an email.
- **Career assistant** chat that knows your profile.

**For employers**
- **Job-description writer.** Turn a title and a few notes into a structured draft with suggested
  tags. It only fills the form; you edit and publish it yourself.
- **Ranked applicants** with a "why?" panel (matched / missing skills, status history). Ranking can
  be switched off.
- **Audit trail** of every status change: who, what, when.
- Tags, job posting, direct messages with applicants.

## How matching works

The score is computed by plain code, not by the AI (`app/Matching/MatchScorer.php`):

```
skills     = |job's skills the seeker has| / |job's skills|
experience = min(1, seeker's years / job's minimum years)

score = round(100 × skills)                              if the job sets no minimum years
score = round(100 × (0.8 × skills + 0.2 × experience))   otherwise
```

- A job's skills are its tags. Skills are compared exactly after normalisation (lower-case plus an
  alias table, e.g. `VueJS` → `vue.js`, in `config/skills.php`).
- If the job lists no skills or the seeker has none, there's no score (not a misleading 0%).
- Scores are cached in `job_matches` and recomputed by queued jobs when a profile or job changes.
  Listing jobs never calls the AI.
- A golden set of resume/job pairs (`tests/Unit/Matching/MatchScorerTest.php`) pins the formula.

The AI only **explains** a score that already exists. It receives skills, years, the job title and
the score — never names, emails or resume text — and its answer is **grounded**: if it mentions a
skill that isn't in your profile or the job, the explanation is replaced by a plain template.

## Human in the loop

Hiring is a high-risk use of AI (EU AI Act; New York City Local Law 144 requires bias audits for
automated hiring tools). NextGig is designed around that:

- **AI never makes a decision.** There is no auto-reject. Every status change is an explicit action
  by a person and is recorded with the actor and time (`application_status_history`, append-only).
- **Ranking uses skills and experience only.** Name, email, photo, gender, age and location are not
  inputs to the score, and contact details are redacted from resumes before any AI call.
- **Transparency.** Applicants are told on the apply form that employers may sort by match score.
  Every AI output is labelled "AI suggestion, not a hiring decision."
- Employers can turn ranking off and see the plain list.

## Security

This started as my first Laravel project. Before adding AI, I audited it and found broken access
control — then fixed each issue test-first (`tests/Feature/Security`):

| Issue | Fix |
| --- | --- |
| Any employer could change the status of **any** application | Only the employer who owns the job (policy) |
| The inbox listed **every user's name and email** | Only people you've talked to; no emails |
| Anyone could self-register as **Admin** | Only Employer / Job Seeker selectable |
| Employers could post jobs under **another employer's** account | Ownership checked on create and update |
| Job pages leaked the employer's login email | Relation no longer sent to the browser |
| Any employer could delete tags other employers use | Tags owned per employer |
| "Delete conversation" wiped it for both people | Hides it only for you |
| No rate limits | Login, registration, password reset, writes and AI calls are throttled; AI also has a daily cap |

## Demo accounts

After seeding demo data (`php artisan demo:reset`):

| Role | Email | Password |
| --- | --- | --- |
| Job seeker | `seeker@nextgig.test` | `password` |
| Employer | `employer@nextgig.test` | `password` |

The seeker has applications at different stages; the employer has a role with ranked applicants.

## Running locally

Requirements: PHP 8.2+ (8.3 recommended), Composer, Node 20+, MySQL.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# set DB_* in .env, then:
php artisan migrate
php artisan demo:reset        # optional: demo data + demo accounts
npm run dev
php artisan serve             # or Laravel Herd
php artisan queue:work        # match recompute + emails
docker compose up -d minio minio-setup   # local S3 for uploads
```

**AI:** set `GEMINI_API_KEY` (and optionally `GEMINI_MODEL`, default `gemini-3.1-flash-lite`).
Without a key, AI features show a friendly "not configured" message; everything else works.
`AI_PER_MINUTE` and `AI_DAILY_CAP` set the per-user limits.

**Uploads (S3 / MinIO):** resumes, the PDF sent with each application and profile photos go
to the private `uploads` disk. `UPLOADS_DISK=s3` stores them in `AWS_BUCKET`; locally that's MinIO
(`docker compose up -d minio minio-setup`, or `brew install minio` and run it with the same
credentials). `UPLOADS_DISK=local` keeps them in `storage/app/private`. Files are never public; they
are streamed through authorised routes. Moving from local to S3: `php artisan uploads:copy-local`.

**Real time:** set `BROADCAST_CONNECTION=pusher` and the `PUSHER_*` / `VITE_PUSHER_*` keys (or
point Echo at Laravel Reverb). With the default `log` driver everything works; updates just appear
on refresh instead of live.

## Tests

```bash
php artisan test
```

Tests use in-memory SQLite and a fake AI provider, so they never touch your database or call
Gemini. CI (`.github/workflows/tests.yml`) runs the suite and a production frontend build on every
pull request.

## Project layout

| Path | What's there |
| --- | --- |
| `app/AI` | `AiProvider` interface, Gemini and fake providers, output validation |
| `app/Resume` | PDF text extraction, PII redaction, skill normalisation, profile parser |
| `app/Matching` | Scorer, cached match service, grounded explainer |
| `app/Employer` | Job-description writer |
| `docs/milestones` | What was built, milestone by milestone, with decisions |
| `docs/architecture` | AI matching and messaging design notes |

# M2 — AI foundation (replace the generic chatbot)

**Goal:** swap the local-only, general-purpose chatbot for an AI layer that is deployable, safe and
built for hiring. Later milestones build on it.

## Why
The current `GPTController` / `SendMessageAction` has four problems:
- It calls Ollama at `localhost:11434` with SSL verification off, so it breaks once deployed.
- It is a generic "helpful assistant" that has nothing to do with jobs.
- It builds prompts by concatenating strings with "Human:/Assistant:" labels, so a user can inject
  fake conversation turns.
- It has no rate limit.

## Scope
- Provider interface (`AiProvider`) with one hosted implementation (Gemini free tier, the same as
  Chattrix). Config goes in `config/services.php`; keys only in `.env`.
- Structured output: ask for JSON matching a schema, and validate it before use.
- Resume ingestion: upload a PDF, extract the text, store a parsed profile (skills, years of
  experience, roles). There is already a `resume` column on `jobseekers`.
- Per-user rate limit and a daily cap on AI calls.
- Remove the generic `/chat` feature, or hide it behind a flag, and delete `ChatMessage` if nothing
  else uses it.

## Out of scope
Match scores (M3), employer features (M4).

## Acceptance criteria
- [x] AI calls work on a deployed server; no `localhost` and no `verify => false`.
- [x] The provider can be swapped through config; tests use a fake provider.
- [x] A resume upload produces a parsed profile the seeker can review and edit.
- [x] Invalid or unparseable AI output is handled gracefully and never shown raw.
- [x] Over the limit returns a friendly 429.

## Tests
- Resume parsing with a fake provider: happy path, bad PDF, provider timeout.
- Only the owner can view or edit their parsed profile.
- Rate limit enforced.

## Before starting
Check that the Gemini model you pick is still available.

## Decisions (2026-11-17)
1. **Keep the chat page as a career assistant** built on the new AI layer (renamed in the nav).
2. **Model:** `gemini-3.1-flash-lite` by default (free tier per Google docs, 2026-11), set with
   `GEMINI_MODEL`. Some new Google projects report "free tier not available"; enable billing if so.

## Notes
Branch `feature/m02-ai-foundation`.

- `app/AI`: `AiProvider` interface (`chat()` with structured turns, `json()` with a schema),
  `GeminiProvider` (HTTPS, key in `x-goog-api-key`, retries on connection errors, logs only
  status codes), `FakeAiProvider` (tests; records every call), `AiOutput::validate()`.
  Select with `AI_PROVIDER=gemini|fake`; key in `GEMINI_API_KEY` (`.env` only).
- Resume: `/resume` (seekers only, no ids in the URL). PDF text via `smalot/pdfparser`; email,
  phone, links and the user's own name/email are **redacted before the AI call**. Parsed profile
  stored in `jobseekers.profile` (JSON), file on the private `local` disk. Seekers can edit
  skills, years, roles and summary. Skills are normalised (`config/skills.php` aliases).
- `jobseekers.resume`, `resume_text` and `profile` are `$hidden`, so they never reach employers
  through applications.
- Limits: `throttle:ai` = 10/min **and** 50/day per user (`AI_PER_MINUTE`, `AI_DAILY_CAP`).
  JSON requests get a friendly 429; Inertia form posts get a redirect with an `ai` error.
- `SeekerProfileUpdated` event fires on upload/edit (M3 listens to it).
- Old Ollama/localhost code removed. `ChatMessage` is kept for the assistant's history.
- Tests: `tests/Feature/AI/*` (provider, resume upload incl. bad PDF / timeout / invalid output /
  redaction, career assistant, daily cap).
- Local note: `composer require` needed `--ignore-platform-req=php` because the lockfile's
  `nette/schema` caps PHP at 8.4 and this machine runs 8.5.


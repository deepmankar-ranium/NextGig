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
- [ ] AI calls work on a deployed server; no `localhost` and no `verify => false`.
- [ ] The provider can be swapped through config; tests use a fake provider.
- [ ] A resume upload produces a parsed profile the seeker can review and edit.
- [ ] Invalid or unparseable AI output is handled gracefully and never shown raw.
- [ ] Over the limit returns a friendly 429.

## Tests
- Resume parsing with a fake provider: happy path, bad PDF, provider timeout.
- Only the owner can view or edit their parsed profile.
- Rate limit enforced.

## Before starting
Check that the Gemini model you pick is still available.

## Unresolved questions
1. Keep the chat page as a "career assistant" on top of this layer, or drop it entirely?

## Notes

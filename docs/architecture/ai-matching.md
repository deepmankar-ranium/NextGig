# AI matching — target architecture

**Status:** reference for M2/M3/M4 work. This is the "how", not the "what" — see
[m02-ai-foundation.md](../milestones/m02-ai-foundation.md),
[m03-explainable-job-match.md](../milestones/m03-explainable-job-match.md) and
[m04-employer-ai.md](../milestones/m04-employer-ai.md) for scope and acceptance criteria.

## Why change it
Current `GPTController` / `SendMessageAction` is a generic chat widget, not a hiring feature:
- Calls local Ollama at `localhost:11434` with `verify => false` — breaks once deployed, and
  `verify => false` is a bad pattern to leave in the codebase regardless.
- Freeform Q&A with no grounding in the platform's own data (no resume, no job listings, no match
  logic) — it doesn't know anything about the user's actual applications.
- Prompt built by string-concatenating chat history — a basic prompt-injection surface.
- No rate limit, so every call is unbounded LLM cost.

## Core principle: separate deterministic scoring from LLM explanation
This is the one architectural rule everything else follows, and it's what keeps AI from making the
hiring decision (see the milestones' rule: **AI never makes the hiring decision**):

1. **Retrieval** — embed job postings and parsed resume/profile data into a vector store (pgvector,
   or whatever's available on the deployed DB). This replaces "chat" with actual semantic search over
   real platform data.
2. **Deterministic scoring** — a plain-code scoring function (skills overlap, years of experience,
   location, salary band) that produces a numeric/structured match result. No LLM in this step. This
   layer is auditable and unit-testable like any other business logic.
3. **LLM explanation** — the LLM receives only the pre-computed score plus the specific matched/missing
   attributes, and generates a natural-language explanation ("matched because 4/5 required skills
   present"). It never receives the full resume free-text and never produces the ranking itself —
   that prevents hallucinated justifications and keeps the provider swappable.

## Target shape

### 1. Provider interface
`AiProvider` interface with one hosted implementation (per M2: Gemini free tier or equivalent).
Config in `config/services.php`, keys only in `.env`, never hardcoded, never `localhost` in a
deployed path.

### 2. Structured output
Ask the provider for JSON matching a schema; validate before use. Never render raw model output
directly — invalid/unparseable output must degrade to a friendly error, not a 500 or garbage text.

### 3. Resume ingestion
Upload → extract text → store a parsed profile (skills, years of experience, roles) that the seeker
can review and edit before it's used for matching. The parsed profile, not the raw resume file, is
what feeds the scoring layer.

### 4. Rate limiting and cost control
- Per-user throttle middleware on any route that triggers a provider call.
- A daily cap per user in addition to the per-request throttle.
- Cache embeddings and scores — don't recompute a job's embedding or a user's match score on every
  page view.

### 5. Evaluation
Before relying on prompt/scoring changes, build a small golden set (~50 resume/job pairs with an
expected outcome) and run it after every change to the scoring weights or prompts. This is how you
catch regressions instead of eyeballing output.

### 6. Logging
Log ids and error codes only. No resume text, no prompts, no raw model output in logs.

## Interface surfaces (replace the chat widget)
- Seeker-facing: "why this match" on a job card / application (M3) — structured, not freeform chat.
- Employer-facing: JD writer + ranked applicant shortlist with explanations (M4) — a human still
  decides; the AI ranks and explains, never auto-rejects/auto-hires.
- Decide explicitly whether the old freeform `/chat` page stays as a thin "career assistant" on top
  of this layer, or is removed — don't leave it running unrelated to the scoring pipeline.

## Explicitly out of scope for now
Multi-agent orchestration, knowledge-graph matching, fine-tuning. Start with embeddings + a plain
reranker function; add complexity only if the golden-set evaluation shows it's needed.

## Open questions
1. Keep the old freeform chat page as a general "career assistant," or drop it once M3 ships?
2. Vector store choice depends on what's available on the deploy target — confirm before M2 starts.

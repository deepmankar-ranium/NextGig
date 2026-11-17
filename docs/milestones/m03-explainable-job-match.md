# M3 — Explainable job match (seekers)

**Goal:** every job shows a seeker *why* they match: a score, the skills that match, what's
missing, and how to close the gap. **This is the headline feature.**

## Scope
- A match score (0–100) per seeker × job. It is computed by code from the parsed profile and the
  job's requirements, not invented by the LLM.
- An LLM-written explanation of the score: matched skills, missing skills, 2–3 concrete tips.
- "Best matches for you" sort and filter on the jobs list.
- Cache results; recompute when the resume or the job changes (queued job).
- Clear label: "AI suggestion, not a hiring decision."

## Out of scope
Anything the employer sees (M4).

## Acceptance criteria
- [x] The score is deterministic for the same inputs, and its formula is documented in the README.
- [x] The explanation only mentions skills that exist in the resume or the job (no invented
      skills).
- [x] Updating the resume refreshes the scores in the background.
- [x] A seeker only sees their own match data.
- [x] The jobs list stays fast: scores come from cache, not live AI calls.

## Tests
- Scoring function (unit): known profile + known job → expected score.
- Explanation grounding: the fake provider's output is checked against the real skill lists.
- Recompute is triggered on resume or job update.
- Another user cannot read your match data.

## Decisions (2026-11-17)
1. **Exact match after normalisation first** (lower-case + alias table in `config/skills.php`),
   no embeddings. MySQL has no vector type and the golden set passes; revisit embeddings only if
   real data shows misses the alias table can't fix.

## Notes
Branch `feature/m03-explainable-job-match`.

**Formula** (`app/Matching/MatchScorer.php`, golden set in `tests/Unit/Matching/MatchScorerTest.php`):

```
skills     = |job tags the seeker has| / |job tags|
experience = min(1, seeker years / job min_years_experience)

score = round(100 * skills)                              if the job sets no minimum years
score = round(100 * (0.8 * skills + 0.2 * experience))   otherwise
score = null                                             if the job has no tags or the seeker no skills
```

- Job requirements = the job's **tags** plus a new optional `job_listings.min_years_experience`
  (added to the create/edit forms).
- Cache: `job_matches` (score, matched, missing, inputs_hash, explanation). Recomputed by queued
  jobs `RecomputeSeekerMatches` (on `SeekerProfileUpdated`) and `RecomputeJobMatches` (job
  create/update). The jobs list joins this table; it never calls the AI.
- Explanation: `POST /Jobs/job/{job}/match/explain` (`throttle:ai`). The AI gets only skills,
  years, title and the score; no name, email or resume text. Grounding: any skill named in the
  summary that isn't in the profile or the job → summary replaced by a template; tips must target
  a missing skill and mention only known skills, or they're dropped. Cached until inputs change.
- UI: match panel on the job page ("Explain my match"), % badge on job cards, "Best matches for
  you" sort, label "AI suggestion, not a hiring decision."


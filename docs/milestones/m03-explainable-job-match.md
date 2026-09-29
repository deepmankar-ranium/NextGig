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
- [ ] The score is deterministic for the same inputs, and its formula is documented in the README.
- [ ] The explanation only mentions skills that exist in the resume or the job (no invented
      skills).
- [ ] Updating the resume refreshes the scores in the background.
- [ ] A seeker only sees their own match data.
- [ ] The jobs list stays fast: scores come from cache, not live AI calls.

## Tests
- Scoring function (unit): known profile + known job → expected score.
- Explanation grounding: the fake provider's output is checked against the real skill lists.
- Recompute is triggered on resume or job update.
- Another user cannot read your match data.

## Unresolved questions
1. Use embeddings for fuzzy skill matching (for example "Laravel" ≈ "PHP framework"), or an
   exact-match skills list first?

## Notes

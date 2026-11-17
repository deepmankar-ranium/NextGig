# M5 — Real-time application timeline

**Goal:** seekers watch their application move live, with no refreshing and no guessing. The
events already exist (`JobApplied`, `ApplicationStatusUpdated`); they just don't broadcast yet.

## Scope
- Status stages: applied → viewed → shortlisted → interview → offer / rejected.
- Store history in an `application_status_history` table (used by the M4 audit trail too).
- Broadcast status changes on the seeker's private `user.{id}` channel (it already exists in
  `routes/channels.php`).
- Timeline UI on "Applied jobs"; toast plus a notification badge on change.
- Email notification for the important stages (shortlisted, interview, offer), queued.
- "Viewed" is recorded automatically the first time the employer opens the application.

## Out of scope
Push notifications on mobile. Interview scheduling.

## Acceptance criteria
- [x] A status change appears on the seeker's screen within a few seconds, without a refresh.
- [x] Only the applicant receives their application events (private channel, authorised).
- [x] The history is append-only, and a stage can't go backwards without an explicit action.
- [x] Emails are sent from the queue, never inside the request.

## Tests
- Event broadcast on status change (`Event::fake`).
- Channel authorisation: another user can't subscribe to your channel.
- History row written for each change; "viewed" recorded exactly once.

## Decisions (2026-11-17)
1. **Seekers see "viewed"**, no setting for now. Easy to add later if employers ask.
2. **No email on rejection**, per scope (important stages only: shortlisted, interview, offer).
   The seeker still sees it live on the timeline.

## Notes
Branch `feature/m05-realtime-timeline`.

- Stages: `applied → viewed → shortlisted → interview → offer`, plus `rejected` from any open
  stage. Column changed from enum to string; existing data mapped `pending → applied`,
  `approved → offer` (migration `2026_11_17_000007`). The 2025 migration was pinned to its
  original enum values so it no longer depends on model constants.
- Rules in `Application::canTransitionTo()`: forward moves (skipping is fine) and rejecting an
  open application are allowed; going back or reopening an offer/rejection needs `reopen=true`
  (a "Reopen…" button with a confirmation). Enforced in the request and in the action.
- "Viewed": `POST /applications/{id}/viewed`, called the first time the employer opens a resume
  or cover letter. Recorded exactly once.
- `ApplicationStatusUpdated` broadcasts as `application.status` on `private-user.{seekerUserId}`
  (payload: application id, job title, status, time; no actor). `Applied Jobs` shows a timeline,
  updates live, flashes the card, shows a toast and a nav badge.
- Emails: `ApplicationStageReached` notification (queued) for shortlisted / interview / offer.
  Replaced the old `ApplicationStatusUpdate` mailable and view.
- History reuses M4's `application_status_history`.
- **Needs a broadcaster in production** (Pusher keys or Reverb) and a queue worker.


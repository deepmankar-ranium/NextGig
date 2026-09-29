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
- [ ] A status change appears on the seeker's screen within a few seconds, without a refresh.
- [ ] Only the applicant receives their application events (private channel, authorised).
- [ ] The history is append-only, and a stage can't go backwards without an explicit action.
- [ ] Emails are sent from the queue, never inside the request.

## Tests
- Event broadcast on status change (`Event::fake`).
- Channel authorisation: another user can't subscribe to your channel.
- History row written for each change; "viewed" recorded exactly once.

## Unresolved questions
1. Should seekers see "viewed"? Some employers dislike it, so make it a setting?

## Notes

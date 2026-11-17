# M1 — Security fixes + tests

**Goal:** close the access-control bugs found in the 2026-09-25 review, and prove each one is
fixed with a test. This is also the interview story: "I audited my first project and found broken
access control."

## Findings to fix

| # | Bug | Where | Expected behaviour |
| --- | --- | --- | --- |
| 1 | Any employer can change the status of **any** application | `UpdateApplicationStatusRequest::authorize()` | Only the employer who owns the application's job |
| 2 | The inbox lists **every user's** name and email to anyone logged in | `DirectMessageService::getUsersForInbox()` | Only people you have a conversation with; no emails |
| 3 | Any employer can delete tags that other employers' jobs use | `DestroyTagRequest` / `DeleteTagsAction` | Decide who owns tags (admin-only, or per employer), then enforce it |
| 4 | "Delete conversation" wipes the chat for **both** users | `DeleteDirectMessagesAction` | Decide: delete only for me (hide it) or for both, then document the choice |
| 5 | `JobPolicy::create()` returns `true` for everyone | `JobPolicy` | Employers only (the Form Request already checks this, so align the two) |
| 6 | No rate limits on login, register, messages or chat | `routes/web.php` | Throttle auth and write routes |

## Out of scope
New features. Route renaming (that's M6).

## Acceptance criteria
- [x] Each finding above has a failing test first, then a fix that makes it pass.
- [x] Employer B gets 403 when updating employer A's application.
- [x] The inbox payload contains no emails and no users you haven't messaged.
- [x] Tag deletion follows the chosen ownership rule.
- [x] A job seeker cannot reach job create, edit or delete (403).
- [x] Throttled routes return 429 once over the limit.

## Tests
- Feature tests for jobs, applications, messages and tags, covering both allowed and denied paths.
- Factories for `Employer`, `JobSeeker`, `JobListing`, `Application`, `DirectMessage` and `Tag`
  where missing.

## Decisions (2026-11-17)
1. **Tags are owned per employer.** New `tags.created_by`. An employer can delete a tag only if
   they created it and no other employer's job uses it. Existing tags have no owner and can't be
   deleted by employers.
2. **Delete conversation = hide it for me only.** New `direct_messages.sender_hidden_at` /
   `receiver_hidden_at`. The other person keeps their copy; a new message brings the chat back.

## Notes
Branch `feature/m01-security-fixes` (2026-11-17). Tests: `tests/Feature/Security/*` (33 tests).

Also found and fixed while writing the tests:
- Anyone could pick **Admin** at registration (`role_id` accepted 1). Now only 2 or 3.
- An employer could post or move a job under **another employer's** `employer_id`.
- `/view-applications` crashed with a 500 for job seekers; now 403.
- The job page sent the employer's **login email** (`employer.user`) to every visitor.
- Users could message themselves.
- Inbox "delete conversation" sent options as the POST body (`router.post(url, opts)`), so
  `onSuccess` never ran.
- `ViewApplication.vue` declared `handleBack` twice, which broke `npm run build`.

Inbox now lists only people you've talked to, plus whoever you open via `/messages/{id}`.
To start a conversation: "Message Employer" on a job page, or the message icon next to an
applicant. The `MessagesDeleted` broadcast is gone (deleting no longer affects the other user).

Rate limits (`AppServiceProvider::configureRateLimiting`): login 5/min per email+IP; register and
password reset 10/min per IP; messages, applications, tags and job posts 30/min per user; AI chat
10/min per user.

Tests run on in-memory SQLite (`phpunit.xml`) instead of the dev MySQL database.


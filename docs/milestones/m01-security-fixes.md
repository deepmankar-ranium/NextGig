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
- [ ] Each finding above has a failing test first, then a fix that makes it pass.
- [ ] Employer B gets 403 when updating employer A's application.
- [ ] The inbox payload contains no emails and no users you haven't messaged.
- [ ] Tag deletion follows the chosen ownership rule.
- [ ] A job seeker cannot reach job create, edit or delete (403).
- [ ] Throttled routes return 429 once over the limit.

## Tests
- Feature tests for jobs, applications, messages and tags, covering both allowed and denied paths.
- Factories for `Employer`, `JobSeeker`, `JobListing`, `Application`, `DirectMessage` and `Tag`
  where missing.

## Unresolved questions
1. Tag ownership: global and admin-managed, or owned by each employer?
2. Conversation delete: hide it just for me (a `deleted_by` column), or keep "delete for both"
   with a clear warning?

## Notes

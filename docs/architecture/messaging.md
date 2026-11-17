# Messaging — target architecture

**Status:** reference for M1/M5 work. This is the "how", not the "what" — see
[m01-security-fixes.md](../milestones/m01-security-fixes.md) and
[m05-realtime-application-timeline.md](../milestones/m05-realtime-application-timeline.md) for scope
and acceptance criteria.

## Why change it
Current implementation (`MessagesController`, `DirectMessageController`, `DirectMessageService`) is a
generic any-user-to-any-user DM box:
- `StoreMessageRequest::authorize()` returns `true` unconditionally — no relationship check.
- `DirectMessageService::getUsersForInbox()` lists every user in the system.
- No shared `MessagePolicy`; ownership checks are duplicated and inconsistent between `destroy()` and
  `deleteMessages()`.
- Broadcasts run inline (not queued), so a Pusher/Reverb failure 500s the request instead of
  degrading gracefully.
- History is loaded with a flat `limit()`, not cursor-paginated.

## Target shape

### 1. Transport: Laravel Reverb
Move off Pusher onto Reverb (first-party Laravel WebSocket server). Same broadcasting abstraction —
swap the driver in `config/broadcasting.php`, no controller changes needed for this part.

### 2. Domain model: conversations tied to a relationship, not raw user pairs
- A `Conversation` belongs to an `Application` (or an employer↔seeker pair scoped to a job), not a
  bare `sender_id`/`receiver_id` tuple.
- `ConversationPolicy` replaces the inline `sender_id === user->id` checks and the FormRequest
  `authorize()` ad-hoc logic. One policy, one place to read the rules.
- Inbox lists conversations the user is actually part of — never "every user in the system."
- System messages (e.g. "Application status changed to Interviewing") are generated from existing
  domain events and inserted into the same thread, instead of living only in email/notifications.

### 3. Delivery reliability
- Broadcasts go through a queued job (`ShouldBroadcast`, queue worker), not inline in the request —
  a broadcast failure must never fail the message send.
- Wrap outbound broadcast calls in try/catch; log and continue if the queue/broadcast layer errors.

### 4. Data access
- Cursor-based pagination for message history (`whereKey < lastId` or Laravel's cursor paginator),
  not `limit()` + offset.
- Unread counts as a denormalized counter (updated on send/read), not counted per render.
- Broadcast payloads use an API Resource / DTO, not the raw `User` model.

### 5. Authorization checklist
- [ ] Every FormRequest touching messages has a real `rules()` method.
- [ ] Every delete/read path checks conversation membership via `ConversationPolicy`, not inline
  conditionals.
- [ ] Channel authorization (`routes/channels.php`) stays scoped to conversation participants only.

## Explicitly out of scope for now
Attachments, group chats, message editing, typing indicators — don't add these on top of the current
flat schema. Land the conversation/policy rework first.

## Open questions
1. Is a conversation always 1:1 tied to a single `Application`, or can an employer and seeker have one
   conversation spanning multiple applications?
2. Do we need conversation archiving/soft-delete instead of hard delete, given each side currently
   deletes for both parties?

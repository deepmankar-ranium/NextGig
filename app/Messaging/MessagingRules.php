<?php

namespace App\Messaging;

use App\Models\Application;
use App\Models\DirectMessage;
use App\Models\JobListing;
use App\Models\User;
use App\Models\UserBlock;

/**
 * Who may message whom. Messages exist for hiring, so a conversation needs a reason:
 *  - a job seeker can contact an employer who has posted a job;
 *  - an employer can contact people who applied to one of their jobs;
 *  - an existing conversation can always continue;
 *  - admins can contact anyone.
 * Seeker↔seeker and employer↔employer cold messages are not allowed. A block (either way)
 * stops new messages, whatever the relationship.
 */
class MessagingRules
{
    public const ROLE_ADMIN = 'Admin';
    public const ROLE_EMPLOYER = 'Employer';
    public const ROLE_SEEKER = 'Job Seeker';

    /** Can $from open a conversation with $to (ignores blocks; the existing thread stays readable). */
    public function canContact(User $from, User $to): bool
    {
        if ($from->is($to)) {
            return false;
        }

        if ($this->haveTalked($from, $to)) {
            return true;
        }

        $fromRole = $from->loadMissing('role')->role?->name;
        $toRole = $to->loadMissing('role')->role?->name;

        if ($fromRole === self::ROLE_ADMIN || $toRole === self::ROLE_ADMIN) {
            return true;
        }

        if ($fromRole === self::ROLE_SEEKER && $toRole === self::ROLE_EMPLOYER) {
            return $to->employer && $to->employer->jobListings()->exists();
        }

        if ($fromRole === self::ROLE_EMPLOYER && $toRole === self::ROLE_SEEKER) {
            return $this->applicationsBetween($from, $to)->isNotEmpty();
        }

        return false;
    }

    /** Can $from send a message to $to right now. */
    public function canSend(User $from, User $to): bool
    {
        return $this->blockState($from, $to) === null && $this->canContact($from, $to);
    }

    /** 'you_blocked' | 'blocked_you' | null, from $me's point of view. */
    public function blockState(User $me, User $other): ?string
    {
        $blocks = UserBlock::where(fn ($q) => $q->where('blocker_id', $me->id)->where('blocked_id', $other->id))
            ->orWhere(fn ($q) => $q->where('blocker_id', $other->id)->where('blocked_id', $me->id))
            ->pluck('blocker_id');

        if ($blocks->contains($me->id)) {
            return 'you_blocked';
        }

        return $blocks->isNotEmpty() ? 'blocked_you' : null;
    }

    public function haveTalked(User $a, User $b): bool
    {
        return DirectMessage::between($a->id, $b->id)->exists();
    }

    /**
     * Applications linking an employer and a seeker (either argument order), newest first.
     *
     * @return \Illuminate\Support\Collection<int, Application>
     */
    public function applicationsBetween(User $a, User $b)
    {
        [$employer, $seeker] = $a->employer ? [$a, $b] : [$b, $a];

        if (! $employer->employer || ! $seeker->jobseeker) {
            return collect();
        }

        return Application::query()
            ->where('jobSeeker_id', $seeker->jobseeker->id)
            ->whereHas('jobListing', fn ($q) => $q->where('employer_id', $employer->employer->id))
            ->with('jobListing:id,title')
            ->latest('id')
            ->get();
    }

    /** A job may be attached to a message only if it belongs to one of the two people. */
    public function jobBelongsToEither(?int $jobId, User $a, User $b): bool
    {
        if ($jobId === null) {
            return true;
        }

        $employerIds = collect([$a->employer?->id, $b->employer?->id])->filter();

        return $employerIds->isNotEmpty()
            && JobListing::whereKey($jobId)->whereIn('employer_id', $employerIds)->exists();
    }
}

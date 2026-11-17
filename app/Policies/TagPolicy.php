<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

/**
 * Tags are owned by the employer who created them. An employer may delete a tag only if
 * they created it and no other employer's job uses it. Tags without an owner (created
 * before ownership existed) cannot be deleted by employers.
 */
class TagPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role?->name === 'Employer';
    }

    public function create(User $user): bool
    {
        return $user->role?->name === 'Employer';
    }

    public function delete(User $user, Tag $tag): bool
    {
        if ($user->role?->name !== 'Employer' || $tag->created_by !== $user->id) {
            return false;
        }

        return ! $tag->jobListings()
            ->whereHas('employer', fn ($q) => $q->where('user_id', '!=', $user->id))
            ->exists();
    }
}

<?php

namespace App\Policies;

use App\Models\DirectMessage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DirectMessagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DirectMessage  $directMessage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, DirectMessage $directMessage)
    {
        return $user->id === $directMessage->sender_id;
    }
}

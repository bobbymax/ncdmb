<?php

namespace App\Policies;

use App\Models\Thread;
use App\Models\User;

class ThreadPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function view(User $user, Thread $t): bool
    {
        return in_array($user->id, [$t->thread_owner_id, $t->recipient_id]);
    }
}

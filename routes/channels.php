<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('threads.{threadId}', function ($user, int $threadId) {
    $t = \App\Models\Thread::find($threadId);
    if (!$t) return false;
    return in_array($user->id, [$t->thread_owner_id, $t->recipient_id]);
});

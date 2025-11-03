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

Broadcast::channel('inbound.{inboundId}', function ($user, int $inboundId) {
    // Check if user has access to this inbound document
    $inbound = \App\Models\Inbound::find($inboundId);
    
    if (!$inbound) {
        return false;
    }
    
    // Allow access if user is authenticated
    return $user !== null;
});

Broadcast::channel('resource.{resourceType}.{resourceId}', function ($user, string $resourceType, int $resourceId) {
    // Check if user has access to this resource
    // For now, allow all authenticated users
    // You can add more specific checks based on resource type
    return $user !== null;
});

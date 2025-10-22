<?php

namespace App\Services;

interface RecipientResolverServiceInterface
{
    public function resolveTrackerRecipients(array $tracker, array $loggedInUser = []): \Illuminate\Support\Collection;
    public function resolveWatcherRecipients(array $watchers): \Illuminate\Support\Collection;
}

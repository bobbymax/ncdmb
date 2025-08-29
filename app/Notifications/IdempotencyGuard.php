<?php

namespace App\Notifications;

use Illuminate\Support\Facades\Cache;

class IdempotencyGuard
{
    public function alreadySent(string $key, int $ttlSec = 86400): bool
    {
        // Atomic add: returns true if added (i.e., NOT seen before)
        $added = Cache::add($key, 1, $ttlSec);
        return !$added;
    }

    public static function key(int $document_id, string $pointer, string $action_performed, int $userId): string
    {
        return 'notif:sent:' . sha1("$document_id|$pointer|$action_performed|$userId");
    }
}

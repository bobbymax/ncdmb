<?php

namespace App\Repositories;

use App\Models\NotificationPreference;

class NotificationPreferenceRepository extends BaseRepository
{
    public function __construct(NotificationPreference $notificationPreference) {
        parent::__construct($notificationPreference);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

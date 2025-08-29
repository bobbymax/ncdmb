<?php

namespace App\Services;

use App\Repositories\NotificationPreferenceRepository;

class NotificationPreferenceService extends BaseService
{
    public function __construct(NotificationPreferenceRepository $notificationPreferenceRepository)
    {
        parent::__construct($notificationPreferenceRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            //
        ];
    }
}

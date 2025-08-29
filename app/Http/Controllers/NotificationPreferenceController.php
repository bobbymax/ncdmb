<?php

namespace App\Http\Controllers;


use App\Http\Resources\NotificationPreferenceResource;
use App\Services\NotificationPreferenceService;

class NotificationPreferenceController extends BaseController
{
    public function __construct(NotificationPreferenceService $notificationPreferenceService) {
        parent::__construct($notificationPreferenceService, 'NotificationPreference', NotificationPreferenceResource::class);
    }
}

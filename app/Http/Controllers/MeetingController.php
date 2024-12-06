<?php

namespace App\Http\Controllers;

use App\Http\Resources\MeetingResource;
use App\Services\MeetingService;

class MeetingController extends BaseController
{
    public function __construct(MeetingService $meetingService) {
        parent::__construct($meetingService, 'Meeting', MeetingResource::class);
    }
}

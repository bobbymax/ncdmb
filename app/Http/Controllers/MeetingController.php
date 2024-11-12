<?php

namespace App\Http\Controllers;

use App\Services\MeetingService;

class MeetingController extends Controller
{
    public function __construct(MeetingService $meetingService) {
        parent::__construct($meetingService, 'Meeting');
    }
}

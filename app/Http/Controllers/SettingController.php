<?php

namespace App\Http\Controllers;

use App\Services\SettingService;

class SettingController extends Controller
{
    public function __construct(SettingService $settingService) {
        parent::__construct($settingService, 'Setting');
    }
}

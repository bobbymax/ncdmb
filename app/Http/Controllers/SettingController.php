<?php

namespace App\Http\Controllers;

use App\Http\Resources\SettingResource;
use App\Services\SettingService;

class SettingController extends BaseController
{
    public function __construct(SettingService $settingService) {
        parent::__construct($settingService, 'Setting', SettingResource::class);
    }
}

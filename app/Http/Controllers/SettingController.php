<?php

namespace App\Http\Controllers;

use App\Http\Resources\SettingResource;
use App\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends BaseController
{
    public function __construct(SettingService $settingService) {
        parent::__construct($settingService, 'Setting', SettingResource::class);
    }

    public function updateConfig(Request $request): \Illuminate\Http\JsonResponse
    {
        $configUpdated = $this->service->updateConfiguration($request->all());
        return $this->success($configUpdated);
    }
}

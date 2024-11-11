<?php

namespace App\Services;

use App\Http\Resources\SettingResource;
use App\Repositories\SettingRepository;

class SettingService extends BaseService
{
    public function __construct(SettingRepository $settingRepository, SettingResource $settingResource)
    {
        parent::__construct($settingRepository, $settingResource);
    }

    public function rules($action = "store"): array
    {
        $rules = [
            'key' => 'required|string',
            'value' => 'nullable|string',
            'details' => 'nullable|string',
            'input_type' => 'required|string|max:255',
            'input_data_type' => 'required|string|max:255',
            'access_group' => 'required|string|in:public,admin',
            'name' => 'required|string|max:255',
        ];

        if ($action == "store") {
            $rules['key'] .= '|unique:settings,key';
        }

        return $rules;
    }
}

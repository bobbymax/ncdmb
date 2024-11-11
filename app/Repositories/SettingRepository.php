<?php

namespace App\Repositories;

use App\Models\Setting;

class SettingRepository extends BaseRepository
{
    public function __construct(Setting $setting) {
        parent::__construct($setting);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

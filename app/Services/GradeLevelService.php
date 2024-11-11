<?php

namespace App\Services;

use App\Http\Resources\GradeLevelResource;
use App\Repositories\GradeLevelRepository;

class GradeLevelService extends BaseService
{
    public function __construct(GradeLevelRepository $gradeLevelRepository, GradeLevelResource $gradeLevelResource)
    {
        parent::__construct($gradeLevelRepository, $gradeLevelResource);
    }

    public function rules($action = "store"): array
    {
        $rules = [
            'key' => 'required|string',
            'name' => 'required|string|max:255',
            'type' => 'required|in:system,board',
        ];

        if ($action == "store") {
            $rules['key'] .= '|unique:grade_levels,key';
        }

        return $rules;
    }
}

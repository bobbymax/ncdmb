<?php

namespace App\Services;

use App\Repositories\GradeLevelRepository;

class GradeLevelService extends BaseService
{
    public function __construct(GradeLevelRepository $gradeLevelRepository)
    {
        parent::__construct($gradeLevelRepository);
    }

    public function rules($action = "store"): array
    {
        $rules = [
            'key' => 'required|string',
            'name' => 'required|string|max:255',
            'type' => 'required|in:system,board',
            'carder_id' => 'required|exists:carders,id',
        ];

        if ($action == "store") {
            $rules['key'] .= '|unique:grade_levels,key';
        }

        return $rules;
    }
}

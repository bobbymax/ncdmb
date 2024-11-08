<?php

namespace App\Services;

use App\Repositories\RNDProjectRepository;

class RNDProjectService extends BaseService
{
    public function __construct(RNDProjectRepository $rNDProjectRepository)
    {
        $this->repository = $rNDProjectRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'project_id' => 'required|integer|exists:projects,id',
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'research_field' => 'required|string|max:255',
            'research_topic' => 'required|string|max:255',
            'research_purpose' => 'required|string|min:5',
            'research_duration' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'completion_date' => 'nullable|date',
            'no_of_nigerians' => 'required|integer|min:1',
            'no_of_expatriates' => 'required|integer|min:1',
            'nc_value' => 'required|numeric|min:0',
            'total_value' => 'required|numeric|min:0',
            'category' => 'required|string|in:statutory,in-house',
            'research_type' => 'required|string|in:basic,applied',
            'research_stream' => 'required|string|in:process-improvement,product-development',
        ];
    }
}

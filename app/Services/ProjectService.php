<?php

namespace App\Services;

use App\Repositories\ProjectRepository;

class ProjectService extends BaseService
{
    public function __construct(ProjectRepository $projectRepository)
    {
        $this->repository = $projectRepository;
    }

    public function rules(): array
    {
        return [
            'operator_id' => 'required|integer|exists:companies,id',
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'completion_date' => 'required|date',
            'approved_amount' => 'required',
        ];
    }
}

<?php

namespace App\Services;

use App\Repositories\ProjectProgramRepository;
use Illuminate\Support\Facades\Auth;

class ProjectProgramService extends BaseService
{
    public function __construct(ProjectProgramRepository $projectProgramRepository)
    {
        parent::__construct($projectProgramRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:3',
            'department_id' => 'required|integer|exists:departments,id',
        ];
    }

    public function store(array $data)
    {
        return parent::store([
            ...$data,
            'user_id' => Auth::id()
        ]);
    }
}

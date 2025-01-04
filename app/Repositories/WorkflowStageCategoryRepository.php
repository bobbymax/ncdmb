<?php

namespace App\Repositories;

use App\Models\WorkflowStageCategory;
use Illuminate\Support\Str;

class WorkflowStageCategoryRepository extends BaseRepository
{
    public function __construct(WorkflowStageCategory $workflowStageCategory) {
        parent::__construct($workflowStageCategory);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'label' => Str::slug($data['name']),
        ];
    }
}

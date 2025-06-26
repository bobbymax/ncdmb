<?php

namespace App\Services;

use App\Repositories\ResourceEditorRepository;

class ResourceEditorService extends BaseService
{
    public function __construct(ResourceEditorRepository $resourceEditorRepository)
    {
        parent::__construct($resourceEditorRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'group_id' => 'required|integer|exists:groups,id',
            'workflow_id' => 'required|integer|exists:workflows,id',
            'workflow_stage_id' => 'required|integer|exists:workflow_stages,id',
            'service_name' => 'required|string|max:255',
            'resource_column_name' => 'required|string|max:255',
            'permission' => 'required|string|in:r,rw,rwx',
            'service_update' => 'required|string|in:d,dr,drn'
        ];
    }
}

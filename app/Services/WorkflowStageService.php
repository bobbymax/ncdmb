<?php

namespace App\Services;

use App\Repositories\DocumentActionRepository;
use App\Repositories\WorkflowStageRepository;
use Illuminate\Support\Facades\DB;

class WorkflowStageService extends BaseService
{
    protected DocumentActionRepository $documentActionRepository;
    public function __construct(WorkflowStageRepository $workflowStageRepository, DocumentActionRepository $documentActionRepository)
    {
        parent::__construct($workflowStageRepository);
        $this->documentActionRepository = $documentActionRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'workflow_id' => 'required|integer|exists:workflows,id',
            'group_id' => 'required|integer|exists:groups,id',
            'name' => 'required|string|max:255',
            'order' => 'required|integer|min:0',
            'actions' => 'required|array',
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $stage = parent::store($data);

            if ($stage) {
                foreach ($data['actions'] as $value) {
                    $action = $this->documentActionRepository->find($value);

                    if ($action && !in_array($action->id, $stage->actions->pluck('id')->toArray())) {
                        $stage->actions()->save($action);
                    }
                }
            }

            return $stage;
        });
    }
}

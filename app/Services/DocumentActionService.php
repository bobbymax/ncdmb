<?php

namespace App\Services;

use App\Repositories\DocumentActionRepository;

class DocumentActionService extends BaseService
{
    public function __construct(DocumentActionRepository $documentActionRepository)
    {
        parent::__construct($documentActionRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'workflow_stage_category_id' => 'required|integer|exists:workflow_stage_categories,id',
            'name' => 'required|string|max:255',
            'button_text' => 'nullable|string|max:255',
            'process_status' => 'required|string|in:next,stall,goto,end,complete',
            'icon' => 'nullable|string|max:255',
            'variant' => 'nullable|string|in:primary,info,success,warning,danger,dark',
            'status' => 'nullable|string|max:255',
            'description' => 'nullable|sometimes|string|min:3',
        ];
    }
}

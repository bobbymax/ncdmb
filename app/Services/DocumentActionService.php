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
            'name' => 'required|string|max:255',
            'button_text' => 'nullable|string|max:255',
            'action_status' => 'required|string|in:passed,failed,attend,appeal,stalled,cancelled,complete',
            'icon' => 'nullable|string|max:255',
            'variant' => 'nullable|string|in:primary,info,success,warning,danger,dark',
            'component' => 'sometimes|string|max:255',
            'mode' => 'nullable|string|in:store,update,destroy',
            'state' => 'nullable|string|in:conditional,fixed',
            'has_update' => 'nullable|boolean',
            'description' => 'nullable|sometimes|string|min:3',
            'carder_id' => 'required|exists:carders,id',
        ];
    }
}

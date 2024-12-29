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
            'url' => 'nullable|string|max:255',
            'frontend_path' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'variant' => 'nullable|string|in:primary,info,success,warning,danger,dark',
            'status' => 'nullable|string|max:255',
            'description' => 'nullable|sometimes|string|min:3',
        ];
    }
}

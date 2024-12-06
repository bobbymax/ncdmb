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
            'description' => 'nullable|sometimes|string|min:3',
        ];
    }
}

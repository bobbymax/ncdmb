<?php

namespace App\Services;

use App\Repositories\DocumentCategoryRepository;

class DocumentCategoryService extends BaseService
{
    public function __construct(DocumentCategoryRepository $documentCategoryRepository)
    {
        parent::__construct($documentCategoryRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'document_type_id' => 'required|integer|exists:document_types,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|sometimes|string|min:3',
            'icon' => 'nullable|sometimes|string',
        ];
    }
}

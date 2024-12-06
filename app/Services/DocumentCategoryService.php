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
            'name' => 'required|string|max:255',
            'description' => 'nullable|sometimes|string|min:3',
        ];
    }
}

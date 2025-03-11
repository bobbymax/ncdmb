<?php

namespace App\Services;

use App\Repositories\DocumentTypeRepository;

class DocumentTypeService extends BaseService
{
    public function __construct(DocumentTypeRepository $documentTypeRepository)
    {
        parent::__construct($documentTypeRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'service' => 'required|string|max:255',
            'description' => 'nullable|sometimes|string|min:3',
            'file_template_id' => 'sometimes|integer|min:0',
        ];
    }
}

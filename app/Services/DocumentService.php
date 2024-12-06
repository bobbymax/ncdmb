<?php

namespace App\Services;

use App\Repositories\DocumentRepository;

class DocumentService extends BaseService
{
    public function __construct(DocumentRepository $documentRepository)
    {
        parent::__construct($documentRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'department_id' => 'required|integer|exists:departments,id',
            'document_category_id' => 'required|integer|exists:document_categories,id',
            'workflow_id' => 'required|integer|exists:workflows,id',
            'document_type_id' => 'required|integer|exists:document_types,id',
            'vendor_id' => 'sometimes|integer',
            'documentable_id' => 'required|integer',
            'documentable_type' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'ref' => 'required|string|max:255',
            'file_path_blob' => 'sometimes|nullable|mimes:pdf|max:4096',
            'status' => 'required|string|in:pending,approved,rejected',
            'is_archived' => 'sometimes|boolean',
        ];
    }
}

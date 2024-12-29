<?php

namespace App\Services;

use App\Repositories\DepartmentRepository;
use App\Repositories\DocumentRepository;
use Carbon\Carbon;

class DocumentService extends BaseService
{
    protected DepartmentRepository $departmentRepository;
    public function __construct(DocumentRepository $documentRepository, DepartmentRepository $departmentRepository)
    {
        parent::__construct($documentRepository);
        $this->departmentRepository = $departmentRepository;
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

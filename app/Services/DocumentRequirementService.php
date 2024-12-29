<?php

namespace App\Services;

use App\Repositories\DocumentRequirementRepository;

class DocumentRequirementService extends BaseService
{
    public function __construct(DocumentRequirementRepository $documentRequirementRepository)
    {
        parent::__construct($documentRequirementRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'priority' => 'required|string|in:low,medium,high',
        ];
    }
}

<?php

namespace App\Services;

use App\Repositories\LegalDocumentRepository;

class LegalDocumentService extends BaseService
{
    public function __construct(LegalDocumentRepository $legalDocumentRepository)
    {
        parent::__construct($legalDocumentRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'project_contract_id' => 'required|integer|exists:project_contracts,id',
            'document_type' => 'required|string|in:contract_draft,signed_contract,addendum,legal_opinion,clearance_certificate,variation_order,termination_notice,other',
            'document_name' => 'required|string|max:255',
            'document_url' => 'required|string|max:500',
            'version' => 'nullable|integer|min:1',
            'uploaded_by' => 'required|integer|exists:users,id',
            'uploaded_at' => 'required|date',
            'is_current' => 'nullable|boolean',
            'requires_signature' => 'nullable|boolean',
            'signed_by' => 'nullable|array',
            'signed_at' => 'nullable|date',
            'description' => 'nullable|string',
        ];
    }
}

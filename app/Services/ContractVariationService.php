<?php

namespace App\Services;

use App\Repositories\ContractVariationRepository;

class ContractVariationService extends BaseService
{
    public function __construct(ContractVariationRepository $contractVariationRepository)
    {
        parent::__construct($contractVariationRepository);
    }

    public function rules($action = "store"): array
    {
        $rules = [
            'project_contract_id' => 'required|integer|exists:project_contracts,id',
            'variation_type' => 'required|string|in:price_adjustment,scope_change,time_extension,specification_change,termination,other',
            'variation_reference' => 'required|string|max:100',
            'original_value' => 'required|numeric|min:0',
            'variation_amount' => 'required|numeric',
            'new_total_value' => 'required|numeric|min:0',
            'reason' => 'required|string',
            'description' => 'nullable|string',
            'initiated_by' => 'required|integer|exists:users,id',
            'initiated_date' => 'required|date',
            'legal_review_id' => 'nullable|integer|exists:legal_reviews,id',
            'approval_status' => 'nullable|string|in:pending,approved,rejected,conditional',
            'approved_by' => 'nullable|integer|exists:users,id',
            'approval_date' => 'nullable|date',
            'approval_notes' => 'nullable|string',
            'rejection_reason' => 'nullable|string',
            'variation_document_url' => 'nullable|string|max:500',
        ];

        if ($action === "store") {
            $rules['variation_reference'] .= '|unique:contract_variations,variation_reference';
        }

        return $rules;
    }
}

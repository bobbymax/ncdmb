<?php

namespace App\Services;

use App\Repositories\ContractDisputeRepository;

class ContractDisputeService extends BaseService
{
    public function __construct(ContractDisputeRepository $contractDisputeRepository)
    {
        parent::__construct($contractDisputeRepository);
    }

    public function rules($action = "store"): array
    {
        $rules = [
            'project_contract_id' => 'required|integer|exists:project_contracts,id',
            'dispute_type' => 'required|string|in:payment,performance,variation,termination,quality,delay,other',
            'dispute_reference' => 'required|string|max:100',
            'description' => 'required|string',
            'raised_by' => 'required|string|in:contractor,government,both',
            'raised_date' => 'required|date',
            'status' => 'nullable|string|in:open,under_negotiation,mediation,arbitration,litigation,resolved,escalated,closed',
            'resolution_method' => 'nullable|string|in:negotiation,mediation,arbitration,litigation,settlement,other',
            'resolved_date' => 'nullable|date|after_or_equal:raised_date',
            'resolution_notes' => 'nullable|string',
            'disputed_amount' => 'nullable|numeric|min:0',
            'resolved_amount' => 'nullable|numeric|min:0',
            'legal_counsel_id' => 'nullable|integer|exists:users,id',
            'legal_advice' => 'nullable|string',
            'supporting_documents' => 'nullable|array',
        ];

        if ($action === "store") {
            $rules['dispute_reference'] .= '|unique:contract_disputes,dispute_reference';
        }

        return $rules;
    }
}

<?php

namespace App\Services;

use App\Repositories\ProcessCardRepository;

class ProcessCardService extends BaseService
{
    public function __construct(ProcessCardRepository $processCardRepository)
    {
        parent::__construct($processCardRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'document_type_id' => 'required|integer|exists:document_types,id',
            'ledger_id' => 'required|integer|exists:ledgers,id',
            'debit_account_id' => 'nullable|integer|exists:chart_of_accounts,id',
            'credit_account_id' => 'nullable|integer|exists:chart_of_accounts,id',
            'service' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'component' => 'required|string|max:255',
            'execution_order' => 'nullable|integer|min:0',
            'rules' => 'nullable|array',
            'validation_rules' => 'nullable|array',
            'auto_reconcile' => 'nullable|boolean',
            'is_disabled' => 'nullable|boolean',
        ];
    }
}

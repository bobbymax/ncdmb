<?php

namespace App\Services;

use App\Repositories\JournalTypeRepository;
use Illuminate\Validation\Rule;

class JournalTypeService extends BaseService
{
    public function __construct(JournalTypeRepository $journalTypeRepository)
    {
        parent::__construct($journalTypeRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'code' => 'required|string|min:3|max:16',
            'description' => 'required|string|min:3',
            'is_taxable' => 'required|boolean',
            'rate' => 'required|numeric|min:0',
            'rate_type' => 'required|string|in:fixed,percent',
            'kind' => 'required|string|in:add,deduct,info',
            'base_selector' => 'required|string|in:GROSS,TAXABLE,NON-TAXABLE,CUSTOM',
            'name' => 'required|string|max:255',
            'fixed_amount' => 'sometimes|numeric|min:0',
            'precedence' => 'required|integer|min:1',
            'rounding' => 'required|string|in:half_up,bankers',
            'context' => 'required|string|in:tax,stamp,commission,holding,gross,net,reimbursement',
            'state' => 'required|string|in:fixed,optional',
            'ledger_id' => 'required|integer|exists:ledgers,id',
            'entity_id' => 'required|integer|exists:entities,id',
            'posting_rules' => 'nullable|array',
            'deductible_from_taxable' => 'required|boolean',
            'is_vat' => 'required|boolean',
            'category' => 'required|string|in:staff,third-party,default',
        ];
    }
}

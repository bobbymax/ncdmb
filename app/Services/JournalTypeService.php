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
            'tax_rate' => 'required|numeric|min:0',
            'deductible' => 'required|string|in:total,taxable,non-taxable',
            'type' => 'required|string|in:debit,credit,both',
            'context' => 'required|string|in:tax,stamp,commission,holding,gross,net,reimbursement',
            'state' => 'required|string|in:fixed,optional',
            'ledger_id' => 'required|integer|exists:ledgers,id',
            'entity_id' => 'required|integer|exists:entities,id',
            'auto_generate_entries' => 'required|boolean',
            'benefactor' => 'required|string|in:beneficiary,entity',
            'category' => 'required|string|in:staff,third-party,default',
            'flag' => 'required|string|in:payable,ledger,retire',
        ];
    }
}

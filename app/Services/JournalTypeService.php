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
            'code' => [
                'required',
                'string',
                'min:3',
                'max:20',
                $action === 'store'
                    ? Rule::unique('journal_types', 'code')
                    : Rule::unique('journal_types', 'code')->ignore($action),
            ],
            'description' => 'required|string|min:3',
            'is_taxable' => 'required|boolean',
            'tax_rate' => 'required|numeric|min:0',
            'deductible_to' => 'required|string|in:total,sub_total,taxable',
            'type' => 'required|string|in:debit,credit,both',
            'auto_generate_entries' => 'required|boolean',
        ];
    }
}

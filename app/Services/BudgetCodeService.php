<?php

namespace App\Services;

use App\Repositories\BudgetCodeRepository;

class BudgetCodeService extends BaseService
{
    public function __construct(
        BudgetCodeRepository $budgetCodeRepository
    ) {
        parent::__construct($budgetCodeRepository);
    }

    public function rules($action = 'store'): array
    {
        $rules = [
            'code' => 'required|string',
        ];

        if ($action === 'store') {
            $rules['code'] .= '|unique:budget_codes,code';
        }

        return $rules;
    }
}

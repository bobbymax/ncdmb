<?php

namespace App\Services;

use App\Repositories\BudgetCodeRepository;

class BudgetCodeService extends BaseService
{
    public function __construct(BudgetCodeRepository $budgetCodeRepository)
    {
        $this->repository = $budgetCodeRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'code' => 'required|string|unique:budget_codes,code',
        ];
    }
}

<?php

namespace App\Services;

use App\Repositories\BudgetHeadRepository;

class BudgetHeadService extends BaseService
{
    public function __construct(BudgetHeadRepository $budgetHeadRepository)
    {
        $this->repository = $budgetHeadRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'code' => 'required|string|unique:budget_heads,code',
            'name' => 'required|string|max:255',
        ];
    }
}

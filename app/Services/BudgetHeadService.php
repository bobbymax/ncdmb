<?php

namespace App\Services;


use App\Repositories\BudgetHeadRepository;

class BudgetHeadService extends BaseService
{
    public function __construct(BudgetHeadRepository $budgetHeadRepository)
    {
        parent::__construct($budgetHeadRepository);
    }

    public function rules($action = "store"): array
    {
        $rules = [
            'code' => 'required|string',
            'name' => 'required|string|max:255',
        ];

        if($action === "store") {
            $rules['code'] .= '|unique:budget_heads,code';
        }

        return $rules;
    }
}

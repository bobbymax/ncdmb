<?php

namespace App\Services;

use App\Http\Resources\BudgetHeadResource;
use App\Repositories\BudgetHeadRepository;

class BudgetHeadService extends BaseService
{
    public function __construct(BudgetHeadRepository $budgetHeadRepository, BudgetHeadResource $budgetHeadResource)
    {
        parent::__construct($budgetHeadRepository, $budgetHeadResource);
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

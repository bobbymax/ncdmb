<?php

namespace App\Services;

use App\Http\Resources\BudgetCodeResource;
use App\Repositories\BudgetCodeRepository;

class BudgetCodeService extends BaseService
{
    public function __construct(
        BudgetCodeRepository $budgetCodeRepository,
        BudgetCodeResource $budgetCodeResource
    ) {
        parent::__construct($budgetCodeRepository, $budgetCodeResource);
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

<?php

namespace App\Services;

use App\Repositories\ExpenseRepository;

class ExpenseService extends BaseService
{
    public function __construct(ExpenseRepository $expenseRepository)
    {
        $this->repository = $expenseRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'claim_id' => 'required|integer|exists:claims,id',
            'remuneration_id' => 'required|integer|exists:remunerations,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'no_of_days' => 'required|integer|min:1',
            'total_amount_spent' => 'required|numeric|min:1',
        ];
    }
}

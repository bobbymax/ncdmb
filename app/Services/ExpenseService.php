<?php

namespace App\Services;


use App\Repositories\ExpenseRepository;

class ExpenseService extends BaseService
{
    public function __construct(ExpenseRepository $expenseRepository)
    {
        parent::__construct($expenseRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'claim_id' => 'required|integer|exists:claims,id',
            'identifier' => 'required|string|unique:expenses,identifier',
            'parent_id' => 'required|integer|exists:allowances,id',
            'allowance_id' => 'required|integer|exists:allowances,id',
            'trip_id' => 'sometimes|integer|min:0',
            'remuneration_id' => 'required|integer|exists:remunerations,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'no_of_days' => 'required|integer|min:1',
            'total_amount_spent' => 'required|numeric|min:1',
            'unit_price' => 'sometimes|numeric|min:1',
            'total_distance_covered' => 'sometimes|numeric|min:0',
            'description' => 'required|string|min:3',
        ];
    }
}

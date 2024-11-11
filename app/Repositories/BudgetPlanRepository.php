<?php

namespace App\Repositories;

use App\Models\BudgetPlan;
use Illuminate\Support\Facades\Auth;

class BudgetPlanRepository extends BaseRepository
{
    public function __construct(BudgetPlan $budgetPlan) {
        parent::__construct($budgetPlan);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'department_id' => Auth::user()->department_id
        ];
    }
}

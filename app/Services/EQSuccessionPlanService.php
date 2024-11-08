<?php

namespace App\Services;

use App\Repositories\EQSuccessionPlanRepository;

class EQSuccessionPlanService extends BaseService
{
    public function __construct(EQSuccessionPlanRepository $eQSuccessionPlanRepository)
    {
        $this->repository = $eQSuccessionPlanRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'expatriate_id' => 'sometimes|integer|exists:e_q_employees,id',
            'understudy_id' => 'sometimes|integer|exists:e_q_employees,id',
            'expatriate_level' => 'sometimes|integer',
            'understudy_level' => 'sometimes|integer',
            'target_level' => 'sometimes|integer',
            'competency' => 'sometimes|string|max:255',
            'year' => 'required|integer|digits:4',
            'period' => 'required|string|max:255',
            'gap_closure_plan' => 'sometimes|string|min:3',
            'description' => 'sometimes|string|min:3',
            'due_date' => 'sometimes|date',
            'remarks' => 'sometimes|string|min:3',
        ];
    }
}

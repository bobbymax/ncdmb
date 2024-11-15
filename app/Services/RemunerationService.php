<?php

namespace App\Services;


use App\Repositories\RemunerationRepository;

class RemunerationService extends BaseService
{
    public function __construct(RemunerationRepository $remunerationRepository)
    {
        parent::__construct($remunerationRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'allowance_id' => 'required|integer|exists:allowances,id',
            'grade_level_id' => 'required|integer|exists:grade_levels,id',
            'amount' => 'required|numeric|min:1',
            'start_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
        ];
    }
}

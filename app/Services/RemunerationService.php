<?php

namespace App\Services;

use App\Http\Resources\RemunerationResource;
use App\Repositories\RemunerationRepository;

class RemunerationService extends BaseService
{
    public function __construct(RemunerationRepository $remunerationRepository, RemunerationResource $remunerationResource)
    {
        parent::__construct($remunerationRepository, $remunerationResource);
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

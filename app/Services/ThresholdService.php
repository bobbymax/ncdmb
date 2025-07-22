<?php

namespace App\Services;

use App\Repositories\ThresholdRepository;

class ThresholdService extends BaseService
{
    public function __construct(ThresholdRepository $thresholdRepository)
    {
        parent::__construct($thresholdRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|string|in:WO,TB,GC,FEC,OTHER'
        ];
    }
}

<?php

namespace App\Services;

use App\Repositories\LifActivityRepository;

class LifActivityService extends BaseService
{
    public function __construct(LifActivityRepository $lifActivityRepository)
    {
        $this->repository = $lifActivityRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'lif_institution_service_id' => 'required|integer|exists:lif_institution_services,id',
            'broker_id' => 'required|integer|exists:brokers,id',
            'amount' => 'required',
            'year' => 'required|integer',
            'period' => 'required|string|max:255',
            'time_frame' => 'required|string|max:255',
            'remarks' => 'sometimes|string|min:5',
        ];
    }
}

<?php

namespace App\Services;

use App\Repositories\BrokerRepository;

class BrokerService extends BaseService
{
    public function __construct(BrokerRepository $brokerRepository)
    {
        $this->repository = $brokerRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'contractor_id' => 'required|integer|exists:companies,id',
            'name' => "required|string",
            'address' => "required|string",
            'phone' => "required|string",
            'email' => "required|string|email|unique:brokers,email",
        ];
    }
}

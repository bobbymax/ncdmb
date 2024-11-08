<?php

namespace App\Services;

use App\Repositories\LifServiceRepository;

class LifServiceService extends BaseService
{
    public function __construct(LifServiceRepository $lifServiceRepository)
    {
        $this->repository = $lifServiceRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}

<?php

namespace App\Services;

use App\Repositories\ServiceTypeRepository;

class ServiceTypeService extends BaseService
{
    public function __construct(ServiceTypeRepository $serviceTypeRepository)
    {
        $this->repository = $serviceTypeRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255'
        ];
    }
}

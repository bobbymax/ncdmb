<?php

namespace App\Services;


use App\Repositories\BuildingRepository;

class BuildingService extends BaseService
{
    public function __construct(
        BuildingRepository $buildingRepository
    ) {
        parent::__construct($buildingRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'no_of_floors' => 'required|integer|min:0',
            'is_decommissioned' => 'sometimes|boolean',
        ];
    }
}

<?php

namespace App\Services;

use App\Http\Resources\BuildingResource;
use App\Repositories\BuildingRepository;

class BuildingService extends BaseService
{
    public function __construct(
        BuildingRepository $buildingRepository,
        BuildingResource $buildingResource
    ) {
        parent::__construct($buildingRepository, $buildingResource);
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

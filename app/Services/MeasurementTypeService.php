<?php

namespace App\Services;

use App\Http\Resources\MeasurementTypeResource;
use App\Repositories\MeasurementTypeRepository;

class MeasurementTypeService extends BaseService
{
    public function __construct(
        MeasurementTypeRepository $measurementTypeRepository,
        MeasurementTypeResource $measurementTypeResource
    ) {
        parent::__construct($measurementTypeRepository, $measurementTypeResource);
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255'
        ];
    }
}

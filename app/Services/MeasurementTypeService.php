<?php

namespace App\Services;


use App\Repositories\MeasurementTypeRepository;

class MeasurementTypeService extends BaseService
{
    public function __construct(
        MeasurementTypeRepository $measurementTypeRepository
    ) {
        parent::__construct($measurementTypeRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255'
        ];
    }
}

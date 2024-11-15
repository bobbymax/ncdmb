<?php

namespace App\Http\Controllers;

use App\Http\Resources\MeasurementTypeResource;
use App\Services\MeasurementTypeService;

class MeasurementTypeController extends Controller
{
    public function __construct(MeasurementTypeService $measurementTypeService) {
        parent::__construct($measurementTypeService, 'MeasurementType', MeasurementTypeResource::class);
    }
}

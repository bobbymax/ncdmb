<?php

namespace App\Http\Controllers;

use App\Services\MeasurementTypeService;

class MeasurementTypeController extends Controller
{
    public function __construct(MeasurementTypeService $measurementTypeService) {
        parent::__construct($measurementTypeService, 'MeasurementType');
    }
}

<?php

namespace App\Http\Controllers;


use App\Http\Resources\CarderResource;
use App\Services\CarderService;

class CarderController extends BaseController
{
    public function __construct(CarderService $carderService) {
        parent::__construct($carderService, 'Carder', CarderResource::class);
    }
}

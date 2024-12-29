<?php

namespace App\Http\Controllers;


use App\Http\Resources\CityResource;
use App\Services\CityService;

class CityController extends BaseController
{
    public function __construct(CityService $cityService) {
        parent::__construct($cityService, 'City', CityResource::class);
    }
}

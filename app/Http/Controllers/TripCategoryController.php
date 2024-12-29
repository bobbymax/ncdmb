<?php

namespace App\Http\Controllers;


use App\Http\Resources\TripCategoryResource;
use App\Services\TripCategoryService;

class TripCategoryController extends BaseController
{
    public function __construct(TripCategoryService $tripCategoryService) {
        parent::__construct($tripCategoryService, 'TripCategory', TripCategoryResource::class);
    }
}

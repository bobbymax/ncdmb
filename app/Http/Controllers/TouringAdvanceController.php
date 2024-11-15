<?php

namespace App\Http\Controllers;

use App\Http\Resources\TouringAdvanceResource;
use App\Services\TouringAdvanceService;

class TouringAdvanceController extends Controller
{
    public function __construct(TouringAdvanceService $touringAdvanceService) {
        parent::__construct($touringAdvanceService, 'Touring Advance',TouringAdvanceResource::class);
    }
}

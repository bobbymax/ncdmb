<?php

namespace App\Http\Controllers;

use App\Services\ServiceTypeService;

class ServiceTypeController extends Controller
{
    public function __construct(ServiceTypeService $serviceTypeService) {
        $this->service = $serviceTypeService;
        $this->name = 'ServiceType';
    }
}

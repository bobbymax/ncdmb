<?php

namespace App\Http\Controllers;


use App\Http\Resources\ApiRequestLogResource;
use App\Services\ApiRequestLogService;

class ApiRequestLogController extends BaseController
{
    public function __construct(ApiRequestLogService $apiRequestLogService) {
        parent::__construct($apiRequestLogService, 'ApiRequestLog', ApiRequestLogResource::class);
    }
}

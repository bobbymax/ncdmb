<?php

namespace App\Http\Controllers;


use App\Http\Resources\OperationResource;
use App\Services\OperationService;

class OperationController extends BaseController
{
    public function __construct(OperationService $operationService) {
        parent::__construct($operationService, 'Operation', OperationResource::class);
    }
}

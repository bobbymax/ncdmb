<?php

namespace App\Http\Controllers;


use App\Http\Resources\ContractDisputeResource;
use App\Services\ContractDisputeService;

class ContractDisputeController extends BaseController
{
    public function __construct(ContractDisputeService $contractDisputeService) {
        parent::__construct($contractDisputeService, 'ContractDispute', ContractDisputeResource::class);
    }
}

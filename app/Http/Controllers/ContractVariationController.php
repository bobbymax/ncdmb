<?php

namespace App\Http\Controllers;


use App\Http\Resources\ContractVariationResource;
use App\Services\ContractVariationService;

class ContractVariationController extends BaseController
{
    public function __construct(ContractVariationService $contractVariationService) {
        parent::__construct($contractVariationService, 'ContractVariation', ContractVariationResource::class);
    }
}

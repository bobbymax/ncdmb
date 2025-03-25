<?php

namespace App\Http\Controllers;


use App\Http\Resources\SignatoryResource;
use App\Services\SignatoryService;

class SignatoryController extends BaseController
{
    public function __construct(SignatoryService $signatoryService) {
        parent::__construct($signatoryService, 'Signatory', SignatoryResource::class);
    }
}

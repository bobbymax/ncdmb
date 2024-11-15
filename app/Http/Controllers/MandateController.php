<?php

namespace App\Http\Controllers;

use App\Http\Resources\MandateResource;
use App\Services\MandateService;

class MandateController extends Controller
{
    public function __construct(MandateService $mandateService) {
        parent::__construct($mandateService, 'Mandate', MandateResource::class);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Resources\RemunerationResource;
use App\Services\RemunerationService;

class RemunerationController extends BaseController
{
    public function __construct(RemunerationService $remunerationService) {
        parent::__construct($remunerationService, 'Remuneration', RemunerationResource::class);
    }
}

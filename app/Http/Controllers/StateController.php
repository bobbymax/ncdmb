<?php

namespace App\Http\Controllers;


use App\Http\Resources\StateResource;
use App\Services\StateService;

class StateController extends BaseController
{
    public function __construct(StateService $stateService) {
        parent::__construct($stateService, 'State', StateResource::class);
    }
}

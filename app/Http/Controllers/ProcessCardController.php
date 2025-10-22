<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProcessCardResource;
use App\Services\ProcessCardService;

class ProcessCardController extends BaseController
{
    public function __construct(ProcessCardService $processCardService) {
        parent::__construct($processCardService, 'ProcessCard', ProcessCardResource::class);
    }
}

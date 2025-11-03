<?php

namespace App\Http\Controllers;


use App\Http\Resources\InboundInstructionResource;
use App\Services\InboundInstructionService;
use Illuminate\Http\Request;

class InboundInstructionController extends BaseController
{
    public function __construct(InboundInstructionService $inboundInstructionService) {
        parent::__construct($inboundInstructionService, 'InboundInstruction', InboundInstructionResource::class);
    }
}

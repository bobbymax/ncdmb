<?php

namespace App\Http\Controllers;


use App\Http\Resources\BlockResource;
use App\Services\BlockService;

class BlockController extends BaseController
{
    public function __construct(BlockService $blockService) {
        parent::__construct($blockService, 'Block', BlockResource::class);
    }
}

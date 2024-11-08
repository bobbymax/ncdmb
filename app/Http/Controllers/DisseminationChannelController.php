<?php

namespace App\Http\Controllers;

use App\Services\DisseminationChannelService;

class DisseminationChannelController extends Controller
{
    public function __construct(DisseminationChannelService $disseminationChannelService) {
        $this->service = $disseminationChannelService;
        $this->name = 'DisseminationChannel';
    }
}

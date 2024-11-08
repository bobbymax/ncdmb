<?php

namespace App\Http\Controllers;

use App\Services\BrokerService;

class BrokerController extends Controller
{
    public function __construct(BrokerService $brokerService) {
        $this->service = $brokerService;
        $this->name = 'Broker';
    }
}

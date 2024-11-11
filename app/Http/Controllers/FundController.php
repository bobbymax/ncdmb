<?php

namespace App\Http\Controllers;

use App\Services\FundService;

class FundController extends Controller
{
    public function __construct(FundService $fundService) {
        $this->service = $fundService;
        $this->name = 'Fund';
    }
}

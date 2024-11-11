<?php

namespace App\Http\Controllers;

use App\Services\FundService;

class FundController extends Controller
{
    public function __construct(FundService $fundService) {
        parent::__construct($fundService, 'Fund');
    }
}

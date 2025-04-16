<?php

namespace App\Http\Controllers;


use App\Http\Resources\ChartOfAccountResource;
use App\Services\ChartOfAccountService;

class ChartOfAccountController extends BaseController
{
    public function __construct(ChartOfAccountService $chartOfAccountService) {
        parent::__construct($chartOfAccountService, 'ChartOfAccount', ChartOfAccountResource::class);
    }
}

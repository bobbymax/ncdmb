<?php

namespace App\Http\Controllers;

use App\Services\CompanyService;

class CompanyController extends Controller
{
    public function __construct(CompanyService $companyService) {
        $this->service = $companyService;
        $this->name = 'Company';
    }
}

<?php

namespace App\Http\Controllers;


use App\Http\Resources\CompanyResource;
use App\Services\CompanyService;

class CompanyController extends BaseController
{
    public function __construct(CompanyService $companyService) {
        parent::__construct($companyService, 'Company', CompanyResource::class);
    }
}

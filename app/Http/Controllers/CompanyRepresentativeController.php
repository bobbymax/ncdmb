<?php

namespace App\Http\Controllers;


use App\Http\Resources\CompanyRepresentativeResource;
use App\Services\CompanyRepresentativeService;

class CompanyRepresentativeController extends BaseController
{
    public function __construct(CompanyRepresentativeService $companyRepresentativeService) {
        parent::__construct($companyRepresentativeService, 'CompanyRepresentative', CompanyRepresentativeResource::class);
    }
}

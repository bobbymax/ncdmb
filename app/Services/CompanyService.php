<?php

namespace App\Services;

use App\Repositories\CompanyRepository;

class CompanyService extends BaseService
{
    public function __construct(CompanyRepository $companyRepository)
    {
        $this->repository = $companyRepository;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:companies',
            'email' => 'required|string|email|max:255|unique:companies',
            'phone' => 'required|string|max:255|unique:companies',
            'ncec_no' => 'required|string|max:255|unique:companies',
            'nogicjqs_cert_no' => 'required|string|max:255|unique:companies',
            'representative' => 'required|string|max:255',
        ];
    }
}

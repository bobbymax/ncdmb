<?php

namespace App\Repositories;

use App\Models\Company;

class CompanyRepository extends BaseRepository
{
    public function __construct(Company $company) {
        parent::__construct($company);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

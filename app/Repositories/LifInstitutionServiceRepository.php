<?php

namespace App\Repositories;

use App\Models\LifInstitutionService;

class LifInstitutionServiceRepository extends BaseRepository
{
    public function __construct(LifInstitutionService $lifInstitutionService) {
        parent::__construct($lifInstitutionService);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

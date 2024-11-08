<?php

namespace App\Repositories;

use App\Models\LifInstitution;

class LifInstitutionRepository extends BaseRepository
{
    public function __construct(LifInstitution $lifInstitution) {
        parent::__construct($lifInstitution);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

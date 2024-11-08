<?php

namespace App\Repositories;

use App\Models\PrincipalInvestigator;

class PrincipalInvestigatorRepository extends BaseRepository
{
    public function __construct(PrincipalInvestigator $principalInvestigator) {
        parent::__construct($principalInvestigator);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

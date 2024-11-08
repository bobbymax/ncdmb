<?php

namespace App\Repositories;

use App\Models\ResearchBudget;

class ResearchBudgetRepository extends BaseRepository
{
    public function __construct(ResearchBudget $researchBudget) {
        parent::__construct($researchBudget);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

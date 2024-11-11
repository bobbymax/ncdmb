<?php

namespace App\Repositories;

use App\Models\BudgetCode;

class BudgetCodeRepository extends BaseRepository
{
    public function __construct(BudgetCode $budgetCode) {
        parent::__construct($budgetCode);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

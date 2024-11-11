<?php

namespace App\Repositories;

use App\Models\BudgetHead;
use Illuminate\Support\Str;

class BudgetHeadRepository extends BaseRepository
{
    public function __construct(BudgetHead $budgetHead) {
        parent::__construct($budgetHead);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'label' => Str::slug($data['name'])
        ];
    }
}

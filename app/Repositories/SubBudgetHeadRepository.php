<?php

namespace App\Repositories;

use App\Models\SubBudgetHead;
use Illuminate\Support\Str;

class SubBudgetHeadRepository extends BaseRepository
{
    public function __construct(SubBudgetHead $subBudgetHead) {
        parent::__construct($subBudgetHead);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'label' => Str::slug($data['name']),
        ];
    }
}

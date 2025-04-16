<?php

namespace App\Repositories;

use App\Models\ChartOfAccount;

class ChartOfAccountRepository extends BaseRepository
{
    public function __construct(ChartOfAccount $chartOfAccount) {
        parent::__construct($chartOfAccount);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'parent_id' => $data['parent_id'] < 1 ? null : $data['parent_id'],
        ];
    }
}

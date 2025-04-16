<?php

namespace App\Repositories;

use App\Models\Ledger;

class LedgerRepository extends BaseRepository
{
    public function __construct(Ledger $ledger) {
        parent::__construct($ledger);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

<?php

namespace App\Repositories;

use App\Models\TouringAdvance;

class TouringAdvanceRepository extends BaseRepository
{
    public function __construct(TouringAdvance $touringAdvance) {
        parent::__construct($touringAdvance);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

<?php

namespace App\Repositories;

use App\Models\CDActivity;

class CDActivityRepository extends BaseRepository
{
    public function __construct(CDActivity $cDActivity) {
        parent::__construct($cDActivity);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

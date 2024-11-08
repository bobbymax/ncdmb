<?php

namespace App\Repositories;

use App\Models\LifActivity;

class LifActivityRepository extends BaseRepository
{
    public function __construct(LifActivity $lifActivity) {
        parent::__construct($lifActivity);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

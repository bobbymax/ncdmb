<?php

namespace App\Repositories;

use App\Models\BoardProjectActivity;

class BoardProjectActivityRepository extends BaseRepository
{
    public function __construct(BoardProjectActivity $boardProjectActivity) {
        parent::__construct($boardProjectActivity);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

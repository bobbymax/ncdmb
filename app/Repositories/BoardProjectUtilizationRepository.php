<?php

namespace App\Repositories;

use App\Models\BoardProjectUtilization;

class BoardProjectUtilizationRepository extends BaseRepository
{
    public function __construct(BoardProjectUtilization $boardProjectUtilization) {
        parent::__construct($boardProjectUtilization);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

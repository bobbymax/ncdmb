<?php

namespace App\Repositories;

use App\Models\LifService;

class LifServiceRepository extends BaseRepository
{
    public function __construct(LifService $lifService) {
        parent::__construct($lifService);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

<?php

namespace App\Repositories;

use App\Models\Milestone;

class MilestoneRepository extends BaseRepository
{
    public function __construct(Milestone $milestone) {
        parent::__construct($milestone);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

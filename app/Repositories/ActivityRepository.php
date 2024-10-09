<?php

namespace App\Repositories;

use App\Models\Activity;

class ActivityRepository extends BaseRepository
{
    public function __construct(Activity $activity) {
        parent::__construct($activity);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

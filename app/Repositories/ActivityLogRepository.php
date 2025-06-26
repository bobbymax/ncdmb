<?php

namespace App\Repositories;

use App\Models\ActivityLog;

class ActivityLogRepository extends BaseRepository
{
    public function __construct(ActivityLog $activityLog) {
        parent::__construct($activityLog);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

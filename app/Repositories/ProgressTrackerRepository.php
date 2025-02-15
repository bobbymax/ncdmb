<?php

namespace App\Repositories;

use App\Models\ProgressTracker;
use Carbon\Carbon;

class ProgressTrackerRepository extends BaseRepository
{
    public function __construct(ProgressTracker $progressTracker) {
        parent::__construct($progressTracker);
    }

    public function parse(array $data): array
    {
        unset($data['actions']);
        unset($data['recipients']);
        return $data;
    }
}

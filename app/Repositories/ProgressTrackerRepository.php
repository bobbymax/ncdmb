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
        return [
            ...$data,
            'date_completed' => isset($data['date_completed']) ? Carbon::parse($data['date_completed']) : null,
        ];
    }
}

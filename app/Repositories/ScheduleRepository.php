<?php

namespace App\Repositories;

use App\Models\Schedule;

class ScheduleRepository extends BaseRepository
{
    public function __construct(Schedule $schedule) {
        parent::__construct($schedule);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

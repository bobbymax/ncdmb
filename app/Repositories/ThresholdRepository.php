<?php

namespace App\Repositories;

use App\Models\Threshold;

class ThresholdRepository extends BaseRepository
{
    public function __construct(Threshold $threshold) {
        parent::__construct($threshold);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

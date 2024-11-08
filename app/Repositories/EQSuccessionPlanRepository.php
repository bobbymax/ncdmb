<?php

namespace App\Repositories;

use App\Models\EQSuccessionPlan;
use Carbon\Carbon;

class EQSuccessionPlanRepository extends BaseRepository
{
    public function __construct(EQSuccessionPlan $eQSuccessionPlan) {
        parent::__construct($eQSuccessionPlan);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'due_date' => Carbon::parse($data['due_date'])
        ];
    }
}

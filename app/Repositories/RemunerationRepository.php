<?php

namespace App\Repositories;

use App\Models\Remuneration;
use Carbon\Carbon;

class RemunerationRepository extends BaseRepository
{
    public function __construct(Remuneration $remuneration) {
        parent::__construct($remuneration);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'start_date' => Carbon::parse($data['start_date']) ?? null,
            'expiration_date' => Carbon::parse($data['expiration_date']) ?? null,
        ];
    }
}

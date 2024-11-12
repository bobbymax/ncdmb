<?php

namespace App\Repositories;

use App\Models\Reserve;
use Carbon\Carbon;

class ReserveRepository extends BaseRepository
{
    public function __construct(Reserve $reserve) {
        parent::__construct($reserve);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'date_reserved_approval_or_denial' => isset($data['date_reserved_approval_or_denial']) ? Carbon::parse($data['date_reserved_approval_or_denial']) : null,
        ];
    }
}

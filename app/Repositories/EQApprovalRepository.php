<?php

namespace App\Repositories;

use App\Models\EQApproval;
use Carbon\Carbon;

class EQApprovalRepository extends BaseRepository
{
    public function __construct(EQApproval $eQApproval) {
        parent::__construct($eQApproval);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'date_applied' => Carbon::parse($data['date_applied']),
            'date_approved' => Carbon::parse($data['date_approved']),
            'moi_date_approved' => Carbon::parse($data['moi_date_approved']),
        ];
    }
}

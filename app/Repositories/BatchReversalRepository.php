<?php

namespace App\Repositories;

use App\Models\BatchReversal;
use Illuminate\Support\Facades\Auth;

class BatchReversalRepository extends BaseRepository
{
    public function __construct(BatchReversal $batchReversal) {
        parent::__construct($batchReversal);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'user_id' => Auth::user()->id,
            'department_id' => Auth::user()->department_id
        ];
    }
}

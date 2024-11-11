<?php

namespace App\Repositories;

use App\Models\StoreSupply;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StoreSupplyRepository extends BaseRepository
{
    public function __construct(StoreSupply $storeSupply) {
        parent::__construct($storeSupply);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'user_id' => Auth::user()->id,
            'delivery_date' => Carbon::parse($data['delivery_date']),
            'expiration_date' => Carbon::parse($data['expiration_date']) ?? null,
        ];
    }
}

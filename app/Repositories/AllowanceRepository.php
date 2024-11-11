<?php

namespace App\Repositories;

use App\Models\Allowance;
use Illuminate\Support\Str;

class AllowanceRepository extends BaseRepository
{
    public function __construct(Allowance $allowance) {
        parent::__construct($allowance);
    }

    public function parse(array $data): array
    {
        unset($data['remunerations']);

        return [
            ...$data,
            'label' => Str::slug($data['name']),
        ];
    }
}

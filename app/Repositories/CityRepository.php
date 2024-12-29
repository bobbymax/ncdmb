<?php

namespace App\Repositories;

use App\Models\City;
use Illuminate\Support\Str;

class CityRepository extends BaseRepository
{
    public function __construct(City $city) {
        parent::__construct($city);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'label' => Str::slug($data['name']),
        ];
    }
}

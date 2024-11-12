<?php

namespace App\Repositories;

use App\Models\Hotel;
use Illuminate\Support\Str;

class HotelRepository extends BaseRepository
{
    public function __construct(Hotel $hotel) {
        parent::__construct($hotel);
    }

    public function parse(array $data): array
    {
        unset($data['grade_levels']);

        return [
            ...$data,
            'label' => Str::slug($data['name']),
        ];
    }
}

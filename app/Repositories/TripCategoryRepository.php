<?php

namespace App\Repositories;

use App\Models\TripCategory;
use Illuminate\Support\Str;

class TripCategoryRepository extends BaseRepository
{
    public function __construct(TripCategory $tripCategory) {
        parent::__construct($tripCategory);
    }

    public function parse(array $data): array
    {
        unset($data['allowances']);

        return [
            ...$data,
            'label' => Str::slug($data['name']),
        ];
    }
}

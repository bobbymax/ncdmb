<?php

namespace App\Repositories;

use App\Models\Carder;
use Illuminate\Support\Str;

class CarderRepository extends BaseRepository
{
    public function __construct(Carder $carder) {
        parent::__construct($carder);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'label' => Str::slug($data['name']),
        ];
    }
}

<?php

namespace App\Repositories;

use App\Models\State;
use Illuminate\Support\Str;

class StateRepository extends BaseRepository
{
    public function __construct(State $state) {
        parent::__construct($state);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'label' => Str::slug($data['name']),
        ];
    }
}

<?php

namespace App\Repositories;

use App\Models\Mandate;
use Illuminate\Support\Facades\Auth;

class MandateRepository extends BaseRepository
{
    public function __construct(Mandate $mandate) {
        parent::__construct($mandate);
    }

    public function parse(array $data): array
    {
        unset($data['itineraries']);

        return [
            ...$data,
            'user_id' => Auth::user()->id,
            'department_id' => Auth::user()->department_id,
            'code' => $this->generate('code', 'MND')
        ];
    }
}

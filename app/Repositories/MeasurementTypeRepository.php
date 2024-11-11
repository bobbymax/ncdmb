<?php

namespace App\Repositories;

use App\Models\MeasurementType;
use Illuminate\Support\Str;

class MeasurementTypeRepository extends BaseRepository
{
    public function __construct(MeasurementType $measurementType) {
        parent::__construct($measurementType);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'label' => Str::slug($data['name']),
        ];
    }
}

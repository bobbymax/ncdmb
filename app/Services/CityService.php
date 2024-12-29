<?php

namespace App\Services;

use App\Repositories\CityRepository;

class CityService extends BaseService
{
    public function __construct(CityRepository $cityRepository)
    {
        parent::__construct($cityRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'allowance_id' => 'sometimes|integer|min:0',
            'is_capital' => 'sometimes|boolean',
            'has_airport' => 'sometimes|boolean',
            'name' => 'required|string'
        ];
    }
}

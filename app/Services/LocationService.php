<?php

namespace App\Services;

use App\Repositories\LocationRepository;

class LocationService extends BaseService
{
    public function __construct(LocationRepository $locationRepository)
    {
        $this->repository = $locationRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'nullable|string|min:3',
        ];
    }
}

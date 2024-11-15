<?php

namespace App\Services;


use App\Repositories\LocationRepository;

class LocationService extends BaseService
{
    public function __construct(LocationRepository $locationRepository)
    {
        parent::__construct($locationRepository);
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

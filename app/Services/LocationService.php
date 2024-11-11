<?php

namespace App\Services;

use App\Http\Resources\LocationResource;
use App\Repositories\LocationRepository;

class LocationService extends BaseService
{
    public function __construct(LocationRepository $locationRepository, LocationResource $locationResource)
    {
        parent::__construct($locationRepository, $locationResource);
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

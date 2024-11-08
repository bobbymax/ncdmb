<?php

namespace App\Repositories;

use App\Models\ServiceType;

class ServiceTypeRepository extends BaseRepository
{
    public function __construct(ServiceType $serviceType) {
        parent::__construct($serviceType);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

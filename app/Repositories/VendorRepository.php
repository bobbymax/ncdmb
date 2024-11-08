<?php

namespace App\Repositories;

use App\Models\Vendor;

class VendorRepository extends BaseRepository
{
    public function __construct(Vendor $vendor) {
        parent::__construct($vendor);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

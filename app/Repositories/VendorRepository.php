<?php

namespace App\Repositories;

use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;

class VendorRepository extends BaseRepository
{
    public function __construct(Vendor $vendor) {
        parent::__construct($vendor);
    }

    public function parse(array $data): array
    {
        unset($data['logo']);
        return $data;
    }
}

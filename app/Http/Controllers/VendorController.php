<?php

namespace App\Http\Controllers;

use App\Http\Resources\VendorResource;
use App\Services\VendorService;

class VendorController extends BaseController
{
    public function __construct(VendorService $vendorService) {
        parent::__construct($vendorService, 'Vendor', VendorResource::class);
    }
}

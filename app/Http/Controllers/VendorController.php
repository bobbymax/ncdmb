<?php

namespace App\Http\Controllers;

use App\Http\Resources\VendorResource;
use App\Services\VendorService;

class VendorController extends Controller
{
    public function __construct(VendorService $vendorService) {
        parent::__construct($vendorService, 'Vendor', VendorResource::class);
    }
}

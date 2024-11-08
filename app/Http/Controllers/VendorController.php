<?php

namespace App\Http\Controllers;

use App\Services\VendorService;

class VendorController extends Controller
{
    public function __construct(VendorService $vendorService) {
        $this->service = $vendorService;
        $this->name = 'Vendor';
    }
}

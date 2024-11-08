<?php

namespace App\Services;

use App\Repositories\VendorRepository;

class VendorService extends BaseService
{
    public function __construct(VendorRepository $vendorRepository)
    {
        $this->repository = $vendorRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'contractor_id' => 'required|integer|exists:companies,id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:vendors,email',
            'phone' => 'required|string|max:255|unique:vendors,phone',
            'representative' => 'required|string|max:255',
            'ncec_no' => 'required|string|max:255|unique:vendors,ncec_no',
            'nogicjqs_cert_no' => 'required|string|max:255|unique:vendors,nogicjqs_cert_no',
            'origin' => 'nullable|string|max:255',
        ];
    }
}

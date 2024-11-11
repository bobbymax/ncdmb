<?php

namespace App\Services;

use App\Http\Resources\VendorResource;
use App\Models\Vendor;
use App\Repositories\UploadRepository;
use App\Repositories\VendorRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VendorService extends BaseService
{
    protected UploadRepository $uploadRepository;

    public function __construct(
        VendorRepository $vendorRepository,
        VendorResource $vendorResource,
        UploadRepository $uploadRepository
    ) {
        parent::__construct($vendorRepository, $vendorResource);
        $this->uploadRepository = $uploadRepository;
    }

    public function rules($action = "store"): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|min:3',
            'representative_name' => 'nullable|string|max:255',
            'authorising_representative' => 'nullable|string|max:255',
            'ncec_no' => 'nullable|string|max:255',
            'reg_no' => 'nullable|string|max:255',
            'tin_number' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'payment_code' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'logo' => 'nullable|sometimes|image|mimes:jpeg,jpg,png|max:2048',
        ];

        if ($action == "store") {
            $rules['email'] .= '|unique:vendors';
            $rules['phone'] .= '|unique:vendors';
            $rules['ncec_no'] .= '|unique:vendors';
            $rules['reg_no'] .= '|unique:vendors';
            $rules['tin_number'] .= '|unique:vendors';
            $rules['bank_account_number'] .= '|unique:vendors';
            $rules['payment_code'] .= '|unique:vendors';
            $rules['website'] .= '|unique:vendors';
        }

        return $rules;
    }

    public function store(array $data)
    {
        return  DB::transaction(function () use ($data) {
            $vendor = parent::store($data);

            if ($vendor && isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
                $file = $data['logo'];
                $filename = Str::slug($vendor->name) . '-' . 'brand-logo' . '-' . time() . '.' . $file->getClientOriginalExtension();
                $mime = $file->getClientMimeType();
                $ext = $file->getClientOriginalExtension();
                $path = $file->store('images/logos', 'public');
                $size = $file->getSize();

                $this->uploadRepository->create([
                    'uploadable_id' => $vendor->id,
                    'uploadable_type' => Vendor::class,
                    'name' => $filename,
                    'path' => $path,
                    'size' => $size,
                    'mime_type' => $mime,
                    'extension' => $ext,
                ]);

                $vendor->update(['logo' => $path]);
            }

            return $vendor;
        });
    }
}

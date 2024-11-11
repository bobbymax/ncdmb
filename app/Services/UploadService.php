<?php

namespace App\Services;

use App\Repositories\UploadRepository;
use App\Repositories\UserRepository;
use App\Repositories\VendorRepository;
use Illuminate\Support\Facades\DB;

class UploadService extends BaseService
{
    public function __construct(UploadRepository $uploadRepository)
    {
        $this->repository = $uploadRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'path' => 'required|string|max:255',
            'size' => 'required|integer|between:1,4096',
            'mime_type' => 'required|string|max:255',
            'extension' => 'required|string|max:255',
            'uploadable_id' => 'required|integer|min:1',
            'uploadable_type' => 'required|string|max:255',
        ];
    }
}

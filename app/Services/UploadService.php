<?php

namespace App\Services;

use App\Http\Resources\UploadResource;
use App\Repositories\UploadRepository;

class UploadService extends BaseService
{
    public function __construct(UploadRepository $uploadRepository, UploadResource $uploadResource)
    {
        parent::__construct($uploadRepository, $uploadResource);
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

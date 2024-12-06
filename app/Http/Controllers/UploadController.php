<?php

namespace App\Http\Controllers;

use App\Http\Resources\UploadResource;
use App\Services\UploadService;

class UploadController extends BaseController
{
    public function __construct(UploadService $uploadService) {
        parent::__construct($uploadService, 'Upload', UploadResource::class);
    }
}

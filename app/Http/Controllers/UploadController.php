<?php

namespace App\Http\Controllers;

use App\Services\UploadService;

class UploadController extends Controller
{
    public function __construct(UploadService $uploadService) {
        $this->service = $uploadService;
        $this->name = 'Upload';
    }
}

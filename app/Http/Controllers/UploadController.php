<?php

namespace App\Http\Controllers;

use App\Services\UploadService;

class UploadController extends Controller
{
    public function __construct(UploadService $uploadService) {
        parent::__construct($uploadService, 'Upload');
    }
}

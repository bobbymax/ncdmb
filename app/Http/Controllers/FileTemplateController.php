<?php

namespace App\Http\Controllers;


use App\Http\Resources\FileTemplateResource;
use App\Services\FileTemplateService;

class FileTemplateController extends BaseController
{
    public function __construct(FileTemplateService $fileTemplateService) {
        parent::__construct($fileTemplateService, 'FileTemplate', FileTemplateResource::class);
    }
}

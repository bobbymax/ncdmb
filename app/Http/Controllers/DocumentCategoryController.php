<?php

namespace App\Http\Controllers;


use App\Http\Resources\DocumentCategoryResource;
use App\Services\DocumentCategoryService;

class DocumentCategoryController extends BaseController
{
    public function __construct(DocumentCategoryService $documentCategoryService) {
        parent::__construct($documentCategoryService, 'DocumentCategory', DocumentCategoryResource::class);
    }
}

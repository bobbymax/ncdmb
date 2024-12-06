<?php

namespace App\Http\Controllers;


use App\Http\Resources\DocumentTypeResource;
use App\Services\DocumentTypeService;

class DocumentTypeController extends BaseController
{
    public function __construct(DocumentTypeService $documentTypeService) {
        parent::__construct($documentTypeService, 'DocumentType', DocumentTypeResource::class);
    }
}

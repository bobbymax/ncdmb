<?php

namespace App\Http\Controllers;


use App\Http\Resources\DocumentResource;
use App\Services\DocumentService;

class DocumentController extends BaseController
{
    public function __construct(DocumentService $documentService) {
        parent::__construct($documentService, 'Document', DocumentResource::class);
    }

    //
}

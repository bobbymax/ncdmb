<?php

namespace App\Http\Controllers;


use App\Http\Resources\DocumentUpdateResource;
use App\Services\DocumentUpdateService;

class DocumentUpdateController extends BaseController
{
    public function __construct(DocumentUpdateService $documentUpdateService) {
        parent::__construct($documentUpdateService, 'DocumentUpdate', DocumentUpdateResource::class);
    }
}

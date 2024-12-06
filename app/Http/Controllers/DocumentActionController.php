<?php

namespace App\Http\Controllers;


use App\Http\Resources\DocumentActionResource;
use App\Services\DocumentActionService;

class DocumentActionController extends BaseController
{
    public function __construct(DocumentActionService $documentActionService) {
        parent::__construct($documentActionService, 'DocumentAction', DocumentActionResource::class);
    }
}

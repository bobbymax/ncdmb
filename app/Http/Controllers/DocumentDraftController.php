<?php

namespace App\Http\Controllers;


use App\Http\Resources\DocumentDraftResource;
use App\Services\DocumentDraftService;

class DocumentDraftController extends BaseController
{
    public function __construct(DocumentDraftService $documentDraftService) {
        parent::__construct($documentDraftService, 'DocumentDraft', DocumentDraftResource::class);
    }
}

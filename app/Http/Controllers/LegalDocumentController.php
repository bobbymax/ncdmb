<?php

namespace App\Http\Controllers;


use App\Http\Resources\LegalDocumentResource;
use App\Services\LegalDocumentService;

class LegalDocumentController extends BaseController
{
    public function __construct(LegalDocumentService $legalDocumentService) {
        parent::__construct($legalDocumentService, 'LegalDocument', LegalDocumentResource::class);
    }
}

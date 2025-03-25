<?php

namespace App\Http\Controllers;


use App\Http\Resources\DocumentTrailResource;
use App\Services\DocumentTrailService;

class DocumentTrailController extends BaseController
{
    public function __construct(DocumentTrailService $documentTrailService) {
        parent::__construct($documentTrailService, 'DocumentTrail', DocumentTrailResource::class);
    }
}

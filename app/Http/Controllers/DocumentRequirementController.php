<?php

namespace App\Http\Controllers;


use App\Http\Resources\DocumentRequirementResource;
use App\Services\DocumentRequirementService;

class DocumentRequirementController extends BaseController
{
    public function __construct(DocumentRequirementService $documentRequirementService) {
        parent::__construct($documentRequirementService, 'DocumentRequirement', DocumentRequirementResource::class);
    }
}

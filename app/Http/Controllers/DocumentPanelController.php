<?php

namespace App\Http\Controllers;


use App\Http\Resources\DocumentPanelResource;
use App\Services\DocumentPanelService;

class DocumentPanelController extends BaseController
{
    public function __construct(DocumentPanelService $documentPanelService) {
        parent::__construct($documentPanelService, 'DocumentPanel', DocumentPanelResource::class);
    }
}

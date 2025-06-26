<?php

namespace App\Http\Controllers;


use App\Http\Resources\ResourceEditorResource;
use App\Services\ResourceEditorService;

class ResourceEditorController extends BaseController
{
    public function __construct(ResourceEditorService $resourceEditorService) {
        parent::__construct($resourceEditorService, 'ResourceEditor', ResourceEditorResource::class);
    }
}

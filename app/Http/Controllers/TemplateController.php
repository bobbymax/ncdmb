<?php

namespace App\Http\Controllers;


use App\Http\Resources\TemplateResource;
use App\Services\TemplateService;

class TemplateController extends BaseController
{
    public function __construct(TemplateService $templateService) {
        parent::__construct($templateService, 'Template', TemplateResource::class);
    }
}

<?php

namespace App\Http\Controllers;


use App\Http\Resources\EntityResource;
use App\Services\EntityService;

class EntityController extends BaseController
{
    public function __construct(EntityService $entityService) {
        parent::__construct($entityService, 'Entity', EntityResource::class);
    }
}

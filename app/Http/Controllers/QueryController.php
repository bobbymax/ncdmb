<?php

namespace App\Http\Controllers;


use App\Http\Resources\QueryResource;
use App\Services\QueryService;

class QueryController extends BaseController
{
    public function __construct(QueryService $queryService) {
        parent::__construct($queryService, 'Query', QueryResource::class);
    }
}

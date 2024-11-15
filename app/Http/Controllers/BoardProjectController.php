<?php

namespace App\Http\Controllers;

use App\Http\Resources\BoardProjectResource;
use App\Services\BoardProjectService;

class BoardProjectController extends Controller
{
    public function __construct(BoardProjectService $boardProjectService) {
        parent::__construct($boardProjectService, 'BoardProject', BoardProjectResource::class);
    }
}

<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProjectBidResource;
use App\Services\ProjectBidService;

class ProjectBidController extends BaseController
{
    public function __construct(ProjectBidService $projectBidService) {
        parent::__construct($projectBidService, 'ProjectBid', ProjectBidResource::class);
    }
}

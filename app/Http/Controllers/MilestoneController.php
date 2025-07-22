<?php

namespace App\Http\Controllers;


use App\Http\Resources\MilestoneResource;
use App\Services\MilestoneService;

class MilestoneController extends BaseController
{
    public function __construct(MilestoneService $milestoneService) {
        parent::__construct($milestoneService, 'Milestone', MilestoneResource::class);
    }
}

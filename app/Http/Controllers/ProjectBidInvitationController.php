<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProjectBidInvitationResource;
use App\Services\ProjectBidInvitationService;

class ProjectBidInvitationController extends BaseController
{
    public function __construct(ProjectBidInvitationService $projectBidInvitationService) {
        parent::__construct($projectBidInvitationService, 'ProjectBidInvitation', ProjectBidInvitationResource::class);
    }
}

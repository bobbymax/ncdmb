<?php

namespace App\Services;

use App\Repositories\ProjectChangeRequestRepository;

class ProjectChangeRequestService extends BaseService
{
    public function __construct(ProjectChangeRequestRepository $projectChangeRequestRepository)
    {
        parent::__construct($projectChangeRequestRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            //
        ];
    }
}

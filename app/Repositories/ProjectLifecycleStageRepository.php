<?php

namespace App\Repositories;

use App\Models\ProjectLifecycleStage;

class ProjectLifecycleStageRepository extends BaseRepository
{
    public function __construct(ProjectLifecycleStage $projectLifecycleStage) {
        parent::__construct($projectLifecycleStage);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

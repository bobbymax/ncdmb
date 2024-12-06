<?php

namespace App\Repositories;

use App\Models\Workflow;

class WorkflowRepository extends BaseRepository
{
    public function __construct(Workflow $workflow) {
        parent::__construct($workflow);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

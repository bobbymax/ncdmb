<?php

namespace App\Repositories;

use App\Handlers\CodeGenerationErrorException;
use App\Models\ProjectMilestone;
use Carbon\Carbon;

class ProjectMilestoneRepository extends BaseRepository
{
    public function __construct(ProjectMilestone $projectMilestone) {
        parent::__construct($projectMilestone);
    }

    /**
     * @throws CodeGenerationErrorException
     */
    public function parse(array $data): array
    {
        return [
            ...$data,
            'code' => $this->generate('code', 'MLS'),
            'expected_completion_date' => Carbon::parse($data['expected_completion_date']),
            'actual_completion_date' => isset($data['actual_completion_date']) ? Carbon::parse($data['actual_completion_date']) : null,
        ];
    }
}

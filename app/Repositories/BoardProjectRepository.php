<?php

namespace App\Repositories;

use App\Models\BoardProject;
use Carbon\Carbon;

class BoardProjectRepository extends BaseRepository
{
    public function __construct(BoardProject $boardProject) {
        parent::__construct($boardProject);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'approval_date' => $data['approval_date'] ? Carbon::parse($data['approval_date']) : null,
            'start_date' => $data['start_date'] ? Carbon::parse($data['start_date']) : null,
            'completion_date' => $data['completion_date'] ? Carbon::parse($data['completion_date']) : null,
        ];
    }
}

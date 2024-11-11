<?php

namespace App\Repositories;

use App\Handlers\CodeGenerationErrorException;
use App\Models\BoardProject;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BoardProjectRepository extends BaseRepository
{
    public function __construct(BoardProject $boardProject) {
        parent::__construct($boardProject);
    }

    /**
     * @throws CodeGenerationErrorException
     */
    public function parse(array $data): array
    {
        return [
            ...$data,
            'code' => $data['code'] ?? $this->generate('code', 'PROJ'),
            'user_id' => Auth::user()->id,
            'department_id' => Auth::user()->department_id,
            'proposed_start_date' => Carbon::parse($data['proposed_start_date']),
            'proposed_completion_date' => Carbon::parse($data['proposed_completion_date']),
            'actual_start_date' => Carbon::parse($data['actual_start_date']) ?? null,
            'actual_completion_date' => Carbon::parse($data['actual_completion_date']) ?? null,
        ];
    }
}

<?php

namespace App\Repositories;

use App\Handlers\CodeGenerationErrorException;
use App\Models\ProjectContract;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ProjectContractRepository extends BaseRepository
{
    public function __construct(ProjectContract $projectContract) {
        parent::__construct($projectContract);
    }

    /**
     * @throws CodeGenerationErrorException
     */
    public function parse(array $data): array
    {
        return [
            ...$data,
            'user_id' => Auth::user()->id,
            'code' => $data['code'] ?? $this->generate('code', 'CTR'),
            'date_of_acceptance' => isset($data['date_of_acceptance']) ? Carbon::parse($data['date_of_acceptance']) : null
        ];
    }
}

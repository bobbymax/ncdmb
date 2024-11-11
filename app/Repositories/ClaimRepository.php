<?php

namespace App\Repositories;

use App\Handlers\CodeGenerationErrorException;
use App\Models\Claim;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ClaimRepository extends BaseRepository
{
    public function __construct(Claim $claim) {
        parent::__construct($claim);
    }

    /**
     * @throws CodeGenerationErrorException
     */
    public function parse(array $data): array
    {
        unset($data['expenses']);

        $type = $data['type'];

        $prefix = match ($type) {
            'retirement' => 'TA',
            default => 'SC'
        };

        return [
            ...$data,
            'user_id' => $data['user_id'] && (int) $data['user_id'] > 0 ? $data['user_id'] : Auth::user()->id,
            'department_id' => Auth::user()->department_id,
            'code' => $data['code'] !== "" ? $data['code'] : $this->generate("code", $prefix),
            'start_date' => Carbon::parse($data['start_date']),
            'completion_date' => Carbon::parse($data['completion_date']),
            'status' => $data['status'] && $data['status'] !== "" ? $data['status'] : ($data['type'] === 'retirement' ? 'pending' : 'registered'),
        ];
    }
}

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
        return [
            ...$data,
            'department_id' => isset($data['department_id']) && (int) $data['department_id'] > 0 ? $data['department_id'] : Auth::user()->department_id,
        ];
    }

    public function all()
    {
        return $this->instanceOfModel()->where('user_id', Auth::id())->latest()->get();
    }
}

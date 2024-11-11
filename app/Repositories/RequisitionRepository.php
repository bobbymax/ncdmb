<?php

namespace App\Repositories;

use App\Handlers\CodeGenerationErrorException;
use App\Models\Requisition;
use Illuminate\Support\Facades\Auth;

class RequisitionRepository extends BaseRepository
{
    public function __construct(Requisition $requisition) {
        parent::__construct($requisition);
    }

    /**
     * @throws CodeGenerationErrorException
     */
    public function parse(array $data): array
    {
        unset($data['items']);

        return [
            ...$data,
            'user_id' => Auth::user()->id,
            'department_id' => Auth::user()->department_id,
            'code' => $data['code'] ?? $this->generate('code', 'RQS')
        ];
    }
}

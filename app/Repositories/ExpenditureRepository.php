<?php

namespace App\Repositories;

use App\Handlers\CodeGenerationErrorException;
use App\Models\Expenditure;
use Illuminate\Support\Facades\Auth;

class ExpenditureRepository extends BaseRepository
{
    public function __construct(Expenditure $expenditure) {
        parent::__construct($expenditure);
    }

    /**
     * @throws CodeGenerationErrorException
     */
    public function parse(array $data): array
    {
        return [
            ...$data,
            'user_id' => Auth::user()->id,
            'department_id' => Auth::user()->department_id,
            'code' => $data['code'] ?? $this->generate('code', 'EXP')
        ];
    }
}

<?php

namespace App\Repositories;

use App\Models\DocumentRequirement;
use Illuminate\Support\Str;

class DocumentRequirementRepository extends BaseRepository
{
    public function __construct(DocumentRequirement $documentRequirement) {
        parent::__construct($documentRequirement);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'label' => Str::slug($data['name']),
        ];
    }
}

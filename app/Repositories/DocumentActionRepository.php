<?php

namespace App\Repositories;

use App\Models\DocumentAction;
use Illuminate\Support\Str;

class DocumentActionRepository extends BaseRepository
{
    public function __construct(DocumentAction $documentAction) {
        parent::__construct($documentAction);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'label' => Str::slug($data['name']),
            'trigger_workflow_id' => $data['trigger_workflow_id'] > 0 ? $data['trigger_workflow_id'] : null,
        ];
    }
}

<?php

namespace App\Repositories;

use App\Models\ResourceEditor;

class ResourceEditorRepository extends BaseRepository
{
    public function __construct(ResourceEditor $resourceEditor) {
        parent::__construct($resourceEditor);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

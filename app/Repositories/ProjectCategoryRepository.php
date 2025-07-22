<?php

namespace App\Repositories;

use App\Models\ProjectCategory;
use Illuminate\Support\Str;

class ProjectCategoryRepository extends BaseRepository
{
    public function __construct(ProjectCategory $projectCategory) {
        parent::__construct($projectCategory);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'label' => Str::slug($data['name']),
        ];
    }
}

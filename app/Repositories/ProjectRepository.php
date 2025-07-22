<?php

namespace App\Repositories;

use App\Models\Project;
use Illuminate\Support\Str;

class ProjectRepository extends BaseRepository
{
    public function __construct(Project $project) {
        parent::__construct($project);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'slug' => Str::slug($data['title']),
        ];
    }
}

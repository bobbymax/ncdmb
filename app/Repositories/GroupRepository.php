<?php

namespace App\Repositories;

use App\Models\Group;
use Illuminate\Support\Str;

class GroupRepository extends BaseRepository
{
    public function __construct(Group $group) {
        parent::__construct($group);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'label' => Str::slug($data['name']),
        ];
    }
}

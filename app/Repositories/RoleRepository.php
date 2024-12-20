<?php

namespace App\Repositories;

use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Str;

class RoleRepository extends BaseRepository
{
    public function __construct(Role $role) {
        parent::__construct($role);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'slug' => Str::slug($data['name']),
            'issued_date' => isset($data['issued_date']) ? Carbon::parse($data['issued_date']) : null,
            'expired_date' => isset($data['expired_date']) ? Carbon::parse($data['expired_date']) : null,
        ];
    }
}

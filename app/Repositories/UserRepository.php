<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    public function __construct(User $user) {
        parent::__construct($user);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}

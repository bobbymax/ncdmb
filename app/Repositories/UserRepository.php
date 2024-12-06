<?php

namespace App\Repositories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class UserRepository extends BaseRepository
{
    public function __construct(User $user) {
        parent::__construct($user);
    }

    public function parse(array $data): array
    {
        $password = strtolower($data['firstname']) . strtolower($data['surname']);
        return [
            ...$data,
            'password' => $data['password'] ?? Hash::make($password),
            'date_joined' => isset($data['date_joined']) ? Carbon::parse($data['date_joined']) : null
        ];
    }
}

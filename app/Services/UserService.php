<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService extends BaseService
{
    public function __construct(UserRepository $userRepository)
    {
        $this->repository = $userRepository;
    }

    public function rules()
    {
        // TODO: Implement rules() method.
    }
}

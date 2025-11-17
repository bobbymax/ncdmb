<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClaimResource;
use App\Http\Resources\UserResource;
use App\Services\UserService;

class UserController extends BaseController
{
    public function __construct(UserService $userService) {
        parent::__construct($userService, 'User', UserResource::class);
    }

    public function claims($userId, $claimId): \Illuminate\Http\JsonResponse
    {
        $user = $this->service->show($userId);
        if (!$user) {
            return $this->error(null, 'User not found', 422);
        }

        return $this->success(ClaimResource::collection($user->claims->where('id', '!=', $claimId)));
    }

    public function grouping($groupId, $departmentId): \Illuminate\Http\JsonResponse
    {
        try {
            $users = $this->service->getUsersByDepartmentAndGroup($groupId, $departmentId);

            return $this->success($this->jsonResource::collection($users));
        } catch (\Exception $e) {
            return $this->error(null, 'Failed to fetch users: ' . $e->getMessage(), 500);
        }
    }
}

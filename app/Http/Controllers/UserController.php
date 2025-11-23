<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClaimResource;
use App\Http\Resources\UserResource;
use App\Services\UserService;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="User management endpoints"
 * )
 */
class UserController extends BaseController
{
    public function __construct(UserService $userService) {
        parent::__construct($userService, 'User', UserResource::class);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{userId}/claims/{claimId}",
     *     summary="Get user claims excluding a specific claim",
     *     tags={"Users"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="claimId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="User not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function claims($userId, $claimId): \Illuminate\Http\JsonResponse
    {
        $user = $this->service->show($userId);
        if (!$user) {
            return $this->error(null, 'User not found', 422);
        }

        return $this->success(ClaimResource::collection($user->claims->where('id', '!=', $claimId)));
    }

    /**
     * @OA\Get(
     *     path="/api/users/{groupId}/{departmentId}",
     *     summary="Get users by group and department",
     *     tags={"Users"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="groupId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="departmentId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
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

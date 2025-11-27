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
     *     path="/api/users",
     *     summary="List all users",
     *     tags={"Users"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        return parent::index();
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Create a new user",
     *     tags={"Users"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UserRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function store(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        // Normalize nullable ID fields before validation
        $data = $request->all();
        $data = $this->normalizeNullableIds($data);
        $request->merge($data);
        
        return parent::store($request);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get a specific user",
     *     tags={"Users"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function show($id): \Illuminate\Http\JsonResponse
    {
        return parent::show($id);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Update a user",
     *     tags={"Users"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UserRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function update(\Illuminate\Http\Request $request, $id): \Illuminate\Http\JsonResponse
    {
        // Normalize nullable ID fields before validation
        $data = $request->all();
        $data = $this->normalizeNullableIds($data);
        $request->merge($data);
        
        return parent::update($request, $id);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Delete a user",
     *     tags={"Users"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        return parent::destroy($id);
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

    /**
     * Normalize nullable ID fields: convert empty strings, "0", or invalid values to null
     * 
     * @param array $data
     * @return array
     */
    private function normalizeNullableIds(array $data): array
    {
        $nullableIdFields = ['grade_level_id', 'department_id', 'role_id', 'location_id', 'default_page_id'];
        
        foreach ($nullableIdFields as $field) {
            if (isset($data[$field])) {
                $value = $data[$field];
                // Convert empty string, "0", 0, or any non-positive integer to null
                if ($value === '' || $value === '0' || $value === 0 || (is_numeric($value) && (int)$value <= 0)) {
                    $data[$field] = null;
                } elseif (is_numeric($value)) {
                    $data[$field] = (int)$value;
                }
            }
        }
        
        return $data;
    }
}

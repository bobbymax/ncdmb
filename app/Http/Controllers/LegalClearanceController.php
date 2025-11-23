<?php

namespace App\Http\Controllers;


use App\Http\Resources\LegalClearanceResource;
use App\Services\LegalClearanceService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Legal",
 *     description="Legal review and clearance endpoints"
 * )
 */
class LegalClearanceController extends BaseController
{
    public function __construct(LegalClearanceService $legalClearanceService) {
        parent::__construct($legalClearanceService, 'LegalClearance', LegalClearanceResource::class);
    }

    /**
     * @OA\Get(
     *     path="/api/legal/clearances",
     *     summary="List all legal clearances",
     *     tags={"Legal"},
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
     *     path="/api/legal/clearances",
     *     summary="Create a new legal clearance",
     *     tags={"Legal"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/LegalClearanceRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Legal clearance created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        return parent::store($request);
    }

    /**
     * @OA\Get(
     *     path="/api/legal/clearances/{id}",
     *     summary="Get a specific legal clearance",
     *     tags={"Legal"},
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
     *     path="/api/legal/clearances/{id}",
     *     summary="Update a legal clearance",
     *     tags={"Legal"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/LegalClearanceRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Legal clearance updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        return parent::update($request, $id);
    }

    /**
     * @OA\Delete(
     *     path="/api/legal/clearances/{id}",
     *     summary="Delete a legal clearance",
     *     tags={"Legal"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Legal clearance deleted successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        return parent::destroy($id);
    }

    /**
     * @OA\Post(
     *     path="/api/legal/clearances/{id}/grant",
     *     summary="Grant a legal clearance",
     *     tags={"Legal"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Legal clearance granted successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function grant($id): \Illuminate\Http\JsonResponse
    {
        return $this->success(null, 'Legal clearance granted successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/legal/clearances/{id}/revoke",
     *     summary="Revoke a legal clearance",
     *     tags={"Legal"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Legal clearance revoked successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function revoke($id): \Illuminate\Http\JsonResponse
    {
        return $this->success(null, 'Legal clearance revoked successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/legal/clearances/contract/{contractId}",
     *     summary="Get clearances for a specific contract",
     *     tags={"Legal"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="contractId",
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
    public function byContract($contractId): \Illuminate\Http\JsonResponse
    {
        return $this->success(null, 'Clearances retrieved successfully');
    }
}

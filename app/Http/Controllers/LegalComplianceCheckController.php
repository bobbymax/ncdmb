<?php

namespace App\Http\Controllers;


use App\Http\Resources\LegalComplianceCheckResource;
use App\Services\LegalComplianceCheckService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Legal",
 *     description="Legal review and clearance endpoints"
 * )
 */
class LegalComplianceCheckController extends BaseController
{
    public function __construct(LegalComplianceCheckService $legalComplianceCheckService) {
        parent::__construct($legalComplianceCheckService, 'LegalComplianceCheck', LegalComplianceCheckResource::class);
    }

    /**
     * @OA\Get(
     *     path="/api/legal/compliance-checks",
     *     summary="List all legal compliance checks",
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
     *     path="/api/legal/compliance-checks",
     *     summary="Create a new legal compliance check",
     *     tags={"Legal"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compliance check created successfully",
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
     *     path="/api/legal/compliance-checks/{id}",
     *     summary="Get a specific legal compliance check",
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
     *     path="/api/legal/compliance-checks/{id}",
     *     summary="Update a legal compliance check",
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
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compliance check updated successfully",
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
     *     path="/api/legal/compliance-checks/{id}",
     *     summary="Delete a legal compliance check",
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
     *         description="Compliance check deleted successfully",
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
     *     path="/api/legal/compliance-checks/contract/{contractId}",
     *     summary="Get compliance checks for a specific contract",
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
        return $this->success(null, 'Compliance checks retrieved successfully');
    }
}

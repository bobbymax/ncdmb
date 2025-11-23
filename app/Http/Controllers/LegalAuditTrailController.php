<?php

namespace App\Http\Controllers;


use App\Http\Resources\LegalAuditTrailResource;
use App\Services\LegalAuditTrailService;

/**
 * @OA\Tag(
 *     name="Legal",
 *     description="Legal review and clearance endpoints"
 * )
 */
class LegalAuditTrailController extends BaseController
{
    public function __construct(LegalAuditTrailService $legalAuditTrailService) {
        parent::__construct($legalAuditTrailService, 'LegalAuditTrail', LegalAuditTrailResource::class);
    }

    /**
     * @OA\Get(
     *     path="/api/legal/audit-trails",
     *     summary="Get all legal audit trails",
     *     tags={"Legal"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        return parent::index();
    }

    /**
     * @OA\Get(
     *     path="/api/legal/audit-trails/contract/{contractId}",
     *     summary="Get audit trails for a specific contract",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function byContract($contractId): \Illuminate\Http\JsonResponse
    {
        return $this->success(null, 'Audit trails retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/legal/audit-trails/project/{projectId}",
     *     summary="Get audit trails for a specific project",
     *     tags={"Legal"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="projectId",
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
     *     )
     * )
     */
    public function byProject($projectId): \Illuminate\Http\JsonResponse
    {
        return $this->success(null, 'Audit trails retrieved successfully');
    }
}

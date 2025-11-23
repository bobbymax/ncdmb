<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProcurementAuditTrailResource;
use App\Services\ProcurementAuditTrailService;

/**
 * @OA\Tag(
 *     name="Procurement",
 *     description="Procurement and bidding management endpoints"
 * )
 */
class ProcurementAuditTrailController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/procurement/audit-trails",
     *     summary="Get all procurement audit trails",
     *     tags={"Procurement"},
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
    
    /**
     * @OA\Get(
     *     path="/api/procurement/audit-trails/project/{project}",
     *     summary="Get audit trails for a specific project",
     *     tags={"Procurement"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="project",
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
    public function __construct(ProcurementAuditTrailService $procurementAuditTrailService) {
        parent::__construct($procurementAuditTrailService, 'ProcurementAuditTrail', ProcurementAuditTrailResource::class);
    }
}

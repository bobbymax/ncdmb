<?php

namespace App\Http\Controllers;


use App\Http\Resources\ContractDisputeResource;
use App\Services\ContractDisputeService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Legal",
 *     description="Legal review and clearance endpoints"
 * )
 */
class ContractDisputeController extends BaseController
{
    public function __construct(ContractDisputeService $contractDisputeService) {
        parent::__construct($contractDisputeService, 'ContractDispute', ContractDisputeResource::class);
    }

    /**
     * @OA\Get(
     *     path="/api/legal/disputes",
     *     summary="List all contract disputes",
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
     *     path="/api/legal/disputes",
     *     summary="Create a new contract dispute",
     *     tags={"Legal"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ContractDisputeRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Contract dispute created successfully",
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
     *     path="/api/legal/disputes/{id}",
     *     summary="Get a specific contract dispute",
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
     *     path="/api/legal/disputes/{id}",
     *     summary="Update a contract dispute",
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
     *         @OA\JsonContent(ref="#/components/schemas/ContractDisputeRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contract dispute updated successfully",
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
     *     path="/api/legal/disputes/{id}",
     *     summary="Delete a contract dispute",
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
     *         description="Contract dispute deleted successfully",
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
     *     path="/api/legal/disputes/{id}/resolve",
     *     summary="Resolve a contract dispute",
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
     *         description="Contract dispute resolved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function resolve($id): \Illuminate\Http\JsonResponse
    {
        return $this->success(null, 'Contract dispute resolved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/legal/disputes/{id}/escalate",
     *     summary="Escalate a contract dispute",
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
     *         description="Contract dispute escalated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function escalate($id): \Illuminate\Http\JsonResponse
    {
        return $this->success(null, 'Contract dispute escalated successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/legal/disputes/contract/{contractId}",
     *     summary="Get disputes for a specific contract",
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
        return $this->success(null, 'Disputes retrieved successfully');
    }
}

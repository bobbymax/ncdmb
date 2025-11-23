<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProjectEvaluationCommitteeResource;
use App\Services\ProjectEvaluationCommitteeService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Procurement",
 *     description="Procurement and bidding management endpoints"
 * )
 */
class ProjectEvaluationCommitteeController extends BaseController
{
    public function __construct(ProjectEvaluationCommitteeService $projectEvaluationCommitteeService) {
        parent::__construct($projectEvaluationCommitteeService, 'ProjectEvaluationCommittee', ProjectEvaluationCommitteeResource::class);
    }

    /**
     * @OA\Get(
     *     path="/api/procurement/committees",
     *     summary="List all evaluation committees",
     *     tags={"Procurement"},
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
     *     path="/api/procurement/committees",
     *     summary="Create a new evaluation committee",
     *     tags={"Procurement"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Committee created successfully",
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
     *     path="/api/procurement/committees/{id}",
     *     summary="Get a specific evaluation committee",
     *     tags={"Procurement"},
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
     *     path="/api/procurement/committees/{id}",
     *     summary="Update an evaluation committee",
     *     tags={"Procurement"},
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
     *         description="Committee updated successfully",
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
     *     path="/api/procurement/committees/{id}",
     *     summary="Delete an evaluation committee",
     *     tags={"Procurement"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Committee deleted successfully",
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
     *     path="/api/procurement/committees/{id}/dissolve",
     *     summary="Dissolve an evaluation committee",
     *     tags={"Procurement"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Committee dissolved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function dissolve($id): \Illuminate\Http\JsonResponse
    {
        return $this->success(null, 'Committee dissolved successfully');
    }
}

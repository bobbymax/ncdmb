<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProjectBidEvaluationResource;
use App\Services\ProjectBidEvaluationService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Procurement",
 *     description="Procurement and bidding management endpoints"
 * )
 */
class ProjectBidEvaluationController extends BaseController
{
    public function __construct(ProjectBidEvaluationService $projectBidEvaluationService) {
        parent::__construct($projectBidEvaluationService, 'ProjectBidEvaluation', ProjectBidEvaluationResource::class);
    }

    /**
     * @OA\Get(
     *     path="/api/procurement/evaluations",
     *     summary="List all bid evaluations",
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
     *     path="/api/procurement/evaluations",
     *     summary="Create a new bid evaluation",
     *     tags={"Procurement"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Evaluation created successfully",
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
     *     path="/api/procurement/evaluations/{id}",
     *     summary="Get a specific bid evaluation",
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
     *     path="/api/procurement/evaluations/{id}",
     *     summary="Update a bid evaluation",
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
     *         description="Evaluation updated successfully",
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
     *     path="/api/procurement/evaluations/{id}",
     *     summary="Delete a bid evaluation",
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
     *         description="Evaluation deleted successfully",
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
     *     path="/api/procurement/evaluations/{id}/submit",
     *     summary="Submit a bid evaluation",
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
     *         description="Evaluation submitted successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function submit($id): \Illuminate\Http\JsonResponse
    {
        return $this->success(null, 'Evaluation submitted successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/procurement/evaluations/{id}/approve",
     *     summary="Approve a bid evaluation",
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
     *         description="Evaluation approved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function approve($id): \Illuminate\Http\JsonResponse
    {
        return $this->success(null, 'Evaluation approved successfully');
    }
}

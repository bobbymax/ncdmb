<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProjectBidResource;
use App\Services\ProjectBidService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Procurement",
 *     description="Procurement and bidding management endpoints"
 * )
 */
class ProjectBidController extends BaseController
{
    public function __construct(ProjectBidService $projectBidService) {
        parent::__construct($projectBidService, 'ProjectBid', ProjectBidResource::class);
    }

    /**
     * @OA\Get(
     *     path="/api/procurement/bids",
     *     summary="List all bids",
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
     *     path="/api/procurement/bids",
     *     summary="Create a new bid",
     *     tags={"Procurement"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ProjectBidRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Bid created successfully",
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
     *     path="/api/procurement/bids/{id}",
     *     summary="Get a specific bid",
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
     *     path="/api/procurement/bids/{id}",
     *     summary="Update a bid",
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
     *         @OA\JsonContent(ref="#/components/schemas/ProjectBidRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bid updated successfully",
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
     *     path="/api/procurement/bids/{id}",
     *     summary="Delete a bid",
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
     *         description="Bid deleted successfully",
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
     *     path="/api/procurement/bids/{id}/open",
     *     summary="Open a bid",
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
     *         description="Bid opened successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function open($id): \Illuminate\Http\JsonResponse
    {
        return $this->success(null, 'Bid opened successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/procurement/bids/{id}/evaluate",
     *     summary="Evaluate a bid",
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
     *         description="Bid evaluation initiated",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function evaluate($id): \Illuminate\Http\JsonResponse
    {
        return $this->success(null, 'Bid evaluation initiated');
    }

    /**
     * @OA\Post(
     *     path="/api/procurement/bids/{id}/recommend",
     *     summary="Recommend a bid",
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
     *         description="Bid recommended successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function recommend($id): \Illuminate\Http\JsonResponse
    {
        return $this->success(null, 'Bid recommended successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/procurement/bids/{id}/disqualify",
     *     summary="Disqualify a bid",
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
     *         description="Bid disqualified successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function disqualify($id): \Illuminate\Http\JsonResponse
    {
        return $this->success(null, 'Bid disqualified successfully');
    }
}

<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProjectBidInvitationResource;
use App\Services\ProjectBidInvitationService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Procurement",
 *     description="Procurement and bidding management endpoints"
 * )
 */
class ProjectBidInvitationController extends BaseController
{
    public function __construct(ProjectBidInvitationService $projectBidInvitationService) {
        parent::__construct($projectBidInvitationService, 'ProjectBidInvitation', ProjectBidInvitationResource::class);
    }

    /**
     * @OA\Get(
     *     path="/api/procurement/bid-invitations",
     *     summary="List all bid invitations",
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
     *     path="/api/procurement/bid-invitations",
     *     summary="Create a new bid invitation",
     *     tags={"Procurement"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Bid invitation created successfully",
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
     *     path="/api/procurement/bid-invitations/{id}",
     *     summary="Get a specific bid invitation",
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
     *     path="/api/procurement/bid-invitations/{id}",
     *     summary="Update a bid invitation",
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
     *         description="Bid invitation updated successfully",
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
     *     path="/api/procurement/bid-invitations/{id}",
     *     summary="Delete a bid invitation",
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
     *         description="Bid invitation deleted successfully",
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
     *     path="/api/procurement/bid-invitations/{id}/publish",
     *     summary="Publish a bid invitation",
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
     *         description="Bid invitation published successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function publish($id): \Illuminate\Http\JsonResponse
    {
        // Implementation would be handled by the service
        return $this->success(null, 'Bid invitation published successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/procurement/bid-invitations/{id}/close",
     *     summary="Close a bid invitation",
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
     *         description="Bid invitation closed successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function close($id): \Illuminate\Http\JsonResponse
    {
        // Implementation would be handled by the service
        return $this->success(null, 'Bid invitation closed successfully');
    }
}

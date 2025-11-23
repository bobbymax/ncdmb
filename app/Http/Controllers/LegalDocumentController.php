<?php

namespace App\Http\Controllers;


use App\Http\Resources\LegalDocumentResource;
use App\Services\LegalDocumentService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Legal",
 *     description="Legal review and clearance endpoints"
 * )
 */
class LegalDocumentController extends BaseController
{
    public function __construct(LegalDocumentService $legalDocumentService) {
        parent::__construct($legalDocumentService, 'LegalDocument', LegalDocumentResource::class);
    }

    /**
     * @OA\Get(
     *     path="/api/legal/documents",
     *     summary="List all legal documents",
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
     *     path="/api/legal/documents",
     *     summary="Create a new legal document",
     *     tags={"Legal"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Legal document created successfully",
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
     *     path="/api/legal/documents/{id}",
     *     summary="Get a specific legal document",
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
     *     path="/api/legal/documents/{id}",
     *     summary="Update a legal document",
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
     *         description="Legal document updated successfully",
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
     *     path="/api/legal/documents/{id}",
     *     summary="Delete a legal document",
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
     *         description="Legal document deleted successfully",
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
     *     path="/api/legal/documents/{id}/sign",
     *     summary="Sign a legal document",
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
     *         description="Legal document signed successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Success")
     *     )
     * )
     */
    public function sign($id): \Illuminate\Http\JsonResponse
    {
        return $this->success(null, 'Legal document signed successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/legal/documents/contract/{contractId}",
     *     summary="Get documents for a specific contract",
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
        return $this->success(null, 'Documents retrieved successfully');
    }
}

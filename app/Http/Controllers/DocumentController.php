<?php

namespace App\Http\Controllers;


use App\Http\Resources\DocumentResource;
use App\Services\DocumentService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Documents",
 *     description="Document management endpoints"
 * )
 */
class DocumentController extends BaseController
{
    public function __construct(DocumentService $documentService) {
        parent::__construct($documentService, 'Document', DocumentResource::class);
    }

    /**
     * @OA\Get(
     *     path="/api/documents",
     *     summary="List all documents",
     *     tags={"Documents"},
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
     *     path="/api/documents",
     *     summary="Create a new document",
     *     tags={"Documents"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/DocumentRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Document created successfully",
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
     *     path="/api/documents/{id}",
     *     summary="Get a specific document",
     *     tags={"Documents"},
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
     *     path="/api/documents/{id}",
     *     summary="Update a document",
     *     tags={"Documents"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Document Title"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="status", type="string", enum={"pending", "approved", "rejected"}, example="approved"),
     *             @OA\Property(property="is_archived", type="boolean", example=false, nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Document updated successfully",
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
     *     path="/api/documents/{id}",
     *     summary="Delete a document",
     *     tags={"Documents"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Document deleted successfully",
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
     *     path="/api/linked/documents/{parentDocumentId}",
     *     summary="Get linked documents for a parent document",
     *     tags={"Documents"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="parentDocumentId",
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
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Document not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function getLinkedDocuments($parentDocumentId): \Illuminate\Http\JsonResponse
    {
        $document = $this->service->show($parentDocumentId);

        if (!$document) {
            return $this->error(null, "Document not found", 404);
        }

        return $this->success($this->jsonResource::collection($document->linkedDocuments));
    }

    /**
     * @OA\Get(
     *     path="/api/collated/{status}/documents",
     *     summary="Get queued documents by status",
     *     tags={"Documents"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", example="pending")
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
    public function queuedDocuments(string $status): \Illuminate\Http\JsonResponse
    {
        return $this->success($this->jsonResource::collection($this->service->collateDocumentsByStatus($status, $this->service->getScope())));
    }
}

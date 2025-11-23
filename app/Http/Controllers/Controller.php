<?php

namespace App\Http\Controllers;

use App\Handlers\DataNotFound;
use App\Handlers\RecordCreationUnsuccessful;
use App\Handlers\ValidationErrors;
use App\Services\BaseService;
use App\Traits\ApiResponse;
use App\Traits\ResourceContainer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Info(
 *     title="NCDMB API Documentation",
 *     version="1.0.0",
 *     description="Comprehensive API documentation for the NCDMB Document Management System. This API provides endpoints for document management, workflow automation, procurement, legal processes, and more.",
 *     @OA\Contact(
 *         email="support@ncdmb.gov.ng"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Laravel Sanctum Bearer Token Authentication. Get your token by logging in via /api/login endpoint."
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="Authentication and authorization endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Users",
 *     description="User management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Documents",
 *     description="Document management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Projects",
 *     description="Project management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Procurement",
 *     description="Procurement and bidding endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Legal",
 *     description="Legal review and clearance endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Roles",
 *     description="Role management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Groups",
 *     description="Group management endpoints"
 * )
 *
 * @OA\Schema(
 *     schema="Error",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="Error message"),
 *     @OA\Property(property="errors", type="object", example={})
 * )
 *
 * @OA\Schema(
 *     schema="Success",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="data", type="object"),
 *     @OA\Property(property="message", type="string", example="Operation successful")
 * )
 */
abstract class Controller
{
    use ApiResponse, ResourceContainer;
}

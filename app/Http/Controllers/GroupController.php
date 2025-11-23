<?php

namespace App\Http\Controllers;


use App\Http\Resources\GroupResource;
use App\Services\GroupService;

/**
 * @OA\Tag(
 *     name="Groups",
 *     description="Group management endpoints"
 * )
 */
class GroupController extends BaseController
{
    public function __construct(GroupService $groupService) {
        parent::__construct($groupService, 'Group', GroupResource::class);
    }
}

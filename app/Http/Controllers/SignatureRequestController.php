<?php

namespace App\Http\Controllers;


use App\Http\Resources\SignatureRequestResource;
use App\Http\Resources\UserResource;
use App\Services\DepartmentService;
use App\Services\GroupService;
use App\Services\SignatureRequestService;
use Illuminate\Support\Facades\Auth;

class SignatureRequestController extends BaseController
{
    public GroupService $groupService;
    public DepartmentService $departmentService;
    public function __construct(
        SignatureRequestService $signatureRequestService,
        GroupService $groupService,
        DepartmentService $departmentService
    ) {
        parent::__construct($signatureRequestService, 'SignatureRequest', SignatureRequestResource::class);
        $this->groupService = $groupService;
        $this->departmentService = $departmentService;
    }

    public function authorisedUsers($groupId, $departmentId): \Illuminate\Http\JsonResponse
    {
        $group = $this->groupService->show($groupId);
        $department = $departmentId > 0 ? $this->departmentService->show($departmentId) : null;
        $filtered = $group->users->where('id', '!=', Auth::id());
        $users = $department ? $filtered->where('department_id', $department->id) : $filtered;

        return $this->success(UserResource::collection($users));
    }
}

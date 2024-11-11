<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;

class UserService extends BaseService
{
    public function __construct(UserRepository $userRepository, UserResource $userResource)
    {
        parent::__construct($userRepository, $userResource);
    }

    public function rules($action = "store"): array
    {
        $rules = [
            'staff_no' => 'sometimes|nullable|string|max:8',
            'firstname' => 'required|string|max:100',
            'middlename' => 'sometimes|nullable|string|max:100',
            'surname' => 'required|string|max:100',
            'grade_level_id' => 'required|integer|exists:grade_levels,id',
            'department_id' => 'required|integer|exists:departments,id',
            'role_id' => 'required|integer|exists:roles,id',
            'email' => 'required|string|email|max:255',
            'location_id' => 'required|integer|exists:locations,id',
            'avatar' => 'nullable|string|max:255',
            'date_joined' => 'nullable|date',
            'gender' => 'required|string|in:male,female',
            'job_title' => 'nullable|string|max:255',
            'type' => 'required|string|in:permanent,contract,adhoc,secondment,support,admin',
        ];

        if ($action == "store") {
            $rules['staff_no'] .= '|unique:users,staff_no';
            $rules['email'] .= '|unique:users,email';
        }

        return $rules;
    }
}

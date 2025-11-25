<?php

namespace App\Services;

use App\Jobs\StaffRecordAdded;
use App\Repositories\GroupRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Laravel\Reverb\Loggers\Log;

class UserService extends BaseService
{
    protected GroupRepository $groupRepository;
    public function __construct(UserRepository $userRepository, GroupRepository $groupRepository)
    {
        parent::__construct($userRepository);
        $this->groupRepository = $groupRepository;
    }

    public function rules($action = "store"): array
    {
        $rules = [
            'staff_no' => 'sometimes|nullable|max:8',
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
            'default_page_id' => 'sometimes|nullable|integer',
            'groups' => 'sometimes|array',
        ];

        if ($action == "store") {
            $rules['staff_no'] .= '|unique:users,staff_no';
            $rules['email'] .= '|unique:users,email';
        }

        return $rules;
    }

    public function getUsersByDepartmentAndGroup(int $groupId, ?int $departmentId = null)
    {
        return $this->repository->getUsersByDepartmentAndGroup($groupId, $departmentId);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $password = strtolower($data['firstname']) . strtolower($data['surname']);
            $groups = $data['groups'] ?? null;
            unset($data['groups']);

            $user = parent::store([
                ...$data,
                'default_page_id' => (int) $data['default_page_id'] > 0 ? $data['default_page_id'] : null,
                'location_id' => (int) $data['location_id'] > 0 ? $data['location_id'] : null,
                'password' => $password,
            ]);

            if (!$user) {
                return null;
            }

            if ($groups) {
                foreach ($groups as $obj) {
                    $group = $this->groupRepository->find($obj['value']);

                    if ($group) {
                        $user->groups()->save($group);
                    }
                }
            }

            StaffRecordAdded::dispatch($user);
            return $user;
        });
    }

    public function update(int $id, array $data, $parsed = true)
    {
        return DB::transaction(function () use ($id, $parsed, $data) {
            $groups = $data['groups'] ?? null;
            unset($data['groups']);

            $user = parent::update($id, [
                'firstname' => $data['firstname'],
                'surname' => $data['surname'],
                'grade_level_id' => $data['grade_level_id'],
                'department_id' => $data['department_id'],
                'role_id' => $data['role_id'],
                'email' => $data['email'],
                'location_id' => $data['location_id'],
                'middlename' => $data['middlename'],
                'gender' => $data['gender'],
                'job_title' => $data['job_title'],
                'type' => $data['type'],
                'default_page_id' => (int) $data['default_page_id'] > 0 ? $data['default_page_id'] : null,
                'blocked' => $data['blocked'],
                'is_admin' => $data['is_admin'] ?? false,
                'is_logged_in' => $data['is_logged_in'] ?? false,
                'date_joined' => $data['date_joined'] ?? null,
                'staff_no' => $data['staff_no'],
                'status' => $data['status'] ?? 'available',
            ], $parsed);

            if (!$user) {
                return null;
            }

            if (isset($groups)) {
                $newGroupIds = array_map(fn($group) => $group['value'], $groups);
                $user->groups()->sync($newGroupIds);
            }

            return $user;
        });
    }
}

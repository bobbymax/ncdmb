<?php

namespace App\Services;

use App\Jobs\StaffRecordAdded;
use App\Repositories\GroupRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;

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
            'default_page_id' => 'sometimes|integer',
            'groups' => 'sometimes|array',
        ];

        if ($action == "store") {
            $rules['staff_no'] .= '|unique:users,staff_no';
            $rules['email'] .= '|unique:users,email';
        }

        return $rules;
    }

    public function index()
    {
        return $this->repository->accessible();
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $password = strtolower($data['firstname']) . strtolower($data['surname']);

            $user = parent::store([
                ...$data,
                'password' => $password,
            ]);

            if (!$user) {
                return null;
            }

            if (isset($data['groups'])) {
                foreach ($data['groups'] as $obj) {
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
            $user = parent::update($id, $data, $parsed);

            if (!$user) {
                return null;
            }

            if (isset($data['groups'])) {
                $currentGroupIds = $user->groups->pluck('id')->toArray();
                $newGroupIds = array_map(fn($group) => $group['value'], $data['groups']);
                $groupsToRemove = array_diff($currentGroupIds, $newGroupIds);

                if (!empty($groupsToRemove)) {
                    $user->groups()->detach($groupsToRemove);
                }

                $groupsToAdd = array_diff($newGroupIds, $currentGroupIds);
                if (!empty($groupsToAdd)) {
                    $user->groups()->attach($groupsToAdd);
                }
            } else {
                $user->groups()->detach();
            }

            return $user;
        });
    }
}

<?php

namespace App\Repositories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class UserRepository extends BaseRepository
{
    public function __construct(User $user) {
        parent::__construct($user);
    }

    public function parse(array $data): array
    {

        return [
            ...$data,
            'date_joined' => isset($data['date_joined']) ? Carbon::parse($data['date_joined']) : null
        ];
    }

    public function getUsersByDepartmentAndGroup(int $groupId, ?int $departmentId = null)
    {
        $query = $this->model
            ->select([
                'users.id',
                'users.firstname',
                'users.surname',
                'users.staff_no',
                'users.email',
                'users.avatar',
                'users.department_id',
                'users.grade_level_id',
                'users.role_id',
                'users.status',
                'users.blocked',
            ])
            ->join('groupables', function ($join) use ($groupId) {
                $join->on('users.id', '=', 'groupables.groupable_id')
                    ->where('groupables.group_id', '=', $groupId)
                    ->where('groupables.groupable_type', '=', User::class);
            })
            ->where('users.blocked', 0); // Exclude blocked users
//            ->where('users.id', '!=', auth()->id()); // Exclude current user

        // Filter by department if provided
        if ($departmentId && $departmentId > 0) {
            $query->where('users.department_id', $departmentId);
        }

        return $query
            ->orderBy('users.surname')
            ->orderBy('users.firstname')
            ->get();
    }

    public function all()
    {
        $period = config('site.jolt_budget_year');

        $query = $this->model->newQuery()
            ->with([
                'groups',
                'department',
                'gradeLevel.remunerations' => function($query) {
                    $query->where('is_active', 1);
                }
            ]);

        // Apply scope-based filtering
        $query = $this->applyScopeFilter($query, $this->getEffectiveScope());
        // Apply budget year filter if column exists
        $query = $this->applyBudgetYearFilter($query, $period);

        return $query->latest()->get();
    }
}

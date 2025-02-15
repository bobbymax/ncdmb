<?php

namespace App\Repositories;

use App\Models\Department;
use App\Models\Document;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DocumentRepository extends BaseRepository
{
    protected Department $department;
    public function __construct(Document $document, Department $department) {
        parent::__construct($document);
        $this->department = $department;
    }

    public function parse(array $data): array
    {
        return $data;
    }

    /**
     * Generate a reference for the document
     *
     * @param int $departmentId
     * @param string $code
     * @param string $prefix
     * @return string
     */
    public function generateRef(int $departmentId, string $code, string $prefix = "AED"): string
    {
        $currentDate = Carbon::now();
        $department = $this->department->find($departmentId);

        if (!$department) {
            return ""; // Handle null department gracefully
        }

        // Construct department hierarchy reference
        $departmentHierarchy = $this->buildDepartmentHierarchy($department);

        // Build reference
        return sprintf(
            "%s%s/%d/%d/%s",
            $departmentHierarchy,
            $prefix,
            $currentDate->year,
            $currentDate->month,
            $code
        );
    }

    public function all()
    {
        $user = Auth::user();
        $accessLevel = $user->role->access_level;
        $userDepartment = $user->department;
        $groupIds = $user->groups->pluck('id')->toArray();

        // Determine the department scope based on access level
        $departmentScope = Cache::remember("user_{$user->id}_department_scope", 3600, function () use ($accessLevel, $userDepartment) {
            return self::getDepartmentScope($accessLevel, $userDepartment);
        });

        // Initialize query with eager loading for performance
        $query = Document::with('drafts');

        // Filter based on user role access level
        if ($accessLevel === 'basic') {
            $query->where('user_id', $user->id);
        } elseif ($accessLevel !== 'sovereign') {
            $query->where('user_id', $user->id)
                ->orWhereIn('department_id', $departmentScope);
        }

        // Filter documents based on user's groups (drafts belonging to user's groups)
        $query->orWhereHas('drafts', function ($draftQuery) use ($groupIds) {
            $draftQuery->whereIn('group_id', $groupIds);
        });

        return $query->latest()->paginate(50);
    }

    /**
     * Get department scope based on user's access level
     *
     * @param string $accessLevel
     * @param Department $userDepartment
     * @return array
     */
    private static function getDepartmentScope(string $accessLevel, Department $userDepartment): array
    {
        return match ($accessLevel) {
            'basic' => [],
            'operative' => [$userDepartment->id],
            'control' => self::getControlDepartments($userDepartment),
            'command' => self::getCommandDepartments($userDepartment),
            'sovereign' => [], // No restriction, fetch all documents
            default => [$userDepartment->id],
        };
    }

    /**
     * Get department scope for 'control' access level
     *
     * @param Department $userDepartment
     * @return array
     */
    private static function getControlDepartments(Department $userDepartment): array
    {
        if ($userDepartment->type === "division") {
            return Department::where('id', $userDepartment->id)
                ->orWhere('parentId', $userDepartment->id)
                ->pluck('id')
                ->toArray();
        } else {
            // If user's department is not a division, find its division parent
            $division = Department::where('id', $userDepartment->parentId)
                ->where('type', 'division')
                ->first();

            return $division ? Department::where('parentId', $division->id)->pluck('id')->toArray() : [];
        }
    }

    /**
     * Get department scope for 'command' access level
     *
     * @param Department $userDepartment
     * @return array
     */
    private static function getCommandDepartments(Department $userDepartment): array
    {
        // If user is already in a directorate, get all divisions and their departments
        if ($userDepartment->type === 'directorate') {
            return Department::where('id', $userDepartment->id)
                ->orWhere('parentId', $userDepartment->id)
                ->pluck('id')
                ->toArray();
        }

        // If user is in a division or department, get the parent directorate
        $directorate = Department::where('id', function ($query) use ($userDepartment) {
            $query->select('parentId')
                ->from('departments')
                ->where('id', $userDepartment->parentId)
                ->where('type', 'division');
        })->first();

        return $directorate ? Department::where('id', $directorate->id)
            ->orWhere('parentId', $directorate->id)
            ->pluck('id')
            ->toArray()
            : [];
    }


    /**
     * Build the hierarchical department reference
     *
     * @param Department $department
     * @return string
     */
    private function buildDepartmentHierarchy(Department $department): string
    {
        $hierarchy = [];

        if ($department->type === 'directorate') {
            $hierarchy[] = $department->abv;
        } elseif ($department->type === 'division') {
            $directorate = $department->parent;
            if ($directorate) {
                $hierarchy[] = $directorate->abv;
            }
            $hierarchy[] = $department->abv;
        } else {
            $division = $department->parent;
            $directorate = $division?->parent;

            if ($directorate) {
                $hierarchy[] = $directorate->abv;
            }
            if ($division) {
                $hierarchy[] = $division->abv;
            }
            $hierarchy[] = $department->abv;
        }

        return implode("/", $hierarchy) . "/";
    }
}

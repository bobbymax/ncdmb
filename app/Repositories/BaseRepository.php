<?php

namespace App\Repositories;


use App\DTO\ProcessedIncomingData;
use App\Handlers\CodeGenerationErrorException;
use App\Handlers\DataNotFound;
use App\Handlers\RecordCreationUnsuccessful;
use App\Interfaces\IRepository;
use App\Models\Expenditure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

abstract class BaseRepository implements IRepository
{
    protected Model $model;

    /**
     * Default scope for this repository
     * Override in child repositories to set a default scope
     */
    protected string $defaultScope = 'personal';

    /**
     * Strict policy flag
     * If true, repository scope takes precedence over user rank
     * If false, user rank takes precedence
     */
    protected bool $strictPolicy = false;


    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    abstract public function parse(array $data): array;

    public function rank(): string
    {
        $user = Auth::user();
        $gradeLevelRank = (int) $user->gradeLevel->rank;

        // Get the highest-ranked group for this user
        $highestGroupRank = $user->groups->min('rank') ?? 0; // Default to 0 if no groups

        // Use the higher rank (lower number = higher access)
        $effectiveRank = min($gradeLevelRank, $highestGroupRank);

        return match ($effectiveRank) {
            1 => 'board',
            2, 3 => 'directorate',
            4, 5, 6, 7, 8, 9 => 'departmental',
            default => 'personal',
        };
    }

    /**
     * Get the effective scope based on user rank and repository policy
     */
    public function getEffectiveScope(): string
    {
        $userRank = $this->rank();

        // If strict policy is enabled, use repository's default scope
        if ($this->strictPolicy) {
            return $this->defaultScope;
        }

        // Otherwise, use the higher access level between user rank and default scope
        return $this->getHigherAccessLevel($userRank, $this->defaultScope);
    }

    /**
     * Determine which scope provides higher access level
     */
    private function getHigherAccessLevel(string $scope1, string $scope2): string
    {
        $accessLevels = [
            'board' => 1,
            'directorate' => 2,
            'departmental' => 3,
            'personal' => 4
        ];

        $level1 = $accessLevels[$scope1] ?? 4;
        $level2 = $accessLevels[$scope2] ?? 4;

        // Return the scope with lower number (higher access)
        return $level1 <= $level2 ? $scope1 : $scope2;
    }

    public function accessible()
    {
        return $this->model->latest()->get();
    }

    public function all()
    {
        $period = config('site.jolt_budget_year');

        $query = $this->model->newQuery();
        // Apply scope-based filtering first
        $query = $this->applyScopeFilter($query, $this->getEffectiveScope());
        // Apply budget year filter if column exists
        $query = $this->applyBudgetYearFilter($query, $period);

        return $query->latest()->get();
    }

    public function owner($id)
    {
        return $this->model->where('user_id', $id)->latest()->get();
    }

    public function department($id)
    {
        return $this->model->where('department_id', $id)->latest()->get();
    }

    public function find($id)
    {
        return $this->model->findOrFail($id);
    }

    public function findMany(array $ids)
    {
        return $this->model->whereIn('id', $ids)->get();
    }

    public function insert(array $data): bool
    {
        return $this->model->newQuery()->insert($data);
    }

    public function whereInQuery(string $field, array $values)
    {
        return $this->model->whereIn($field, $values);
    }

    public function whereIn(string $field, array $values)
    {
        return $this->whereInQuery($field, $values)->get();
    }

    public function deleteWhereIn(string $field, array $values)
    {
        return $this->model->whereIn($field, $values)->delete();
    }

    /**
     * @throws \Exception
     */
    public function create(array $data)
    {
        $record = $this->model->create($this->parse($data));

        if (!$record) {
            throw new RecordCreationUnsuccessful();
        }

        return $record;
    }

    /**
     * @throws DataNotFound
     */
    public function update(int $id, array $data, bool $parse = true)
    {
        $record = $this->find($id);

        if (!$record) {
            throw new DataNotFound();
        }

        $record->update($parse ? $this->parse($data) : $data);

        return $record;
    }

    /**
     * @throws DataNotFound
     * @throws \Exception
     */
    public function consolidate(ProcessedIncomingData $data)
    {
        return $data;
    }

    public function inBatchQueue($departmentId, $status)
    {
        $drafts =  $this->model->where('department_id', $departmentId)
            ->where('status', $status)
            ->where('document_draftable_type', Expenditure::class)
            ->latest()
            ->with([
                'documentDraftable.expenditureable', // Eager load
            ])
            ->get();

        // Extract only the expenditures from drafts
        // Optional: in case some are null
        return $drafts->map(function ($draft) {
            $expenditure = $draft->documentDraftable;

            if ($expenditure) {
                $expenditure->trackable_draft_id = $draft->id;
            }

            return $expenditure;
        })->filter();
    }

    public function computeTotalAmount(int $id): mixed
    {
        if ($id < 1) return null;

        $record = $this->find($id);
        return $record ?? null;
    }

    /**
     * @throws DataNotFound
     */
    public function destroy(int $id): bool
    {
        $record = $this->find($id);

        if (!$record) {
            throw new DataNotFound();
        }

        return $record->delete();
    }

    /**
     * Generate a unique code with the given prefix and column.
     * @throws CodeGenerationErrorException
     */
    public function generate(string $column, string $prefix, $transaction = false): string
    {
        try {
            do {
                $code = $prefix . random_int(10000, 99999);
                Log::info($code);
            } while ($this->model->withTrashed()->where($column, $code)->exists());

            return $code;
        } catch (QueryException $e) {
            throw new CodeGenerationErrorException('Error generating unique code.', 0, $e);
        }
    }

    public function getRecordByColumn(string $column, mixed $value, string $operator = '=')
    {
        return $this->model->where($column, $operator, $value)->first();
    }

    /*
     * @scope - personal, departmental, directorate, board
     */
    public function getCollectionByColumn(
        string $column,
        mixed $value,
        string $operator = '=',
        $scope = "personal",
        $budget_year = 0
    ): \Illuminate\Database\Eloquent\Collection {

        $period = $budget_year < 1 ? config('site.jolt_budget_year') : $budget_year;

        $query = $this->model->newQuery();
        // Apply scope-based filtering first
        $query = $this->applyScopeFilter($query, $scope);

        // Then apply the column condition
        $query->where($column, $operator, $value);

        // Apply budget year filter if column exists
        $query = $this->applyBudgetYearFilter($query, $period);

        return $query->latest()->get();
    }

    /**
     * Apply budget year filtering to the query
     * Only applies if budget_year column exists
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyBudgetYearFilter($query, $period): \Illuminate\Database\Eloquent\Builder
    {
        $tableColumns = $this->getTableColumns();

        // Check if budget_year column exists
        if (in_array('budget_year', $tableColumns)) {
            if ($period) {
                $query->where('budget_year', $period);
            }
        }

        return $query;
    }

    protected function applyScopeFilter($query, string $scope)
    {
        // If scope is board, no filtering needed
        if ($scope === 'board') {
            return $query;
        }

        $user = Auth::user();

        if (!$user) {
            throw new UnauthorizedHttpException('User not authenticated');
        }

        // Check if required columns exist and fallback to board if they don't
        $fallbackScope = $this->shouldFallbackToBoard($scope);
        if ($fallbackScope) {
            Log::info("Scope '{$scope}' not applicable for model " . get_class($this->model) . ", falling back to board scope");
            return $query; // Return unfiltered query (board scope)
        }

        return match ($scope) {
            'personal' => $this->applyPersonalScope($query, $user),
            'departmental' => $this->applyDepartmentalScope($query, $user),
            'directorate' => $this->applyDirectorateScope($query, $user),
            default => throw new \InvalidArgumentException("Invalid scope: {$scope}"),
        };
    }

    protected function shouldFallbackToBoard(string $scope): bool
    {
        $tableColumns = $this->getTableColumns();

        return match ($scope) {
            'personal' => !in_array('user_id', $tableColumns),
            'departmental', 'directorate' => !in_array('department_id', $tableColumns),
            default => false,
        };
    }

    /**
     * Apply an array of filter conditions to the builder instance.
     *
     * Supports simple [column => value] maps or verbose definitions:
     * ['column' => ..., 'operator' => ..., 'value' => ..., 'boolean' => ...].
     */
    protected function applyConditions(Builder $query, array $conditions): Builder
    {
        foreach ($conditions as $key => $condition) {
            if (is_array($condition) && isset($condition['column'])) {
                $column   = $condition['column'];
                $operator = $condition['operator'] ?? '=';
                $value    = $condition['value'] ?? null;
                $boolean  = $condition['boolean'] ?? 'and';

                $normalizedOperator = strtolower($operator);

                if ($normalizedOperator === 'in' && is_array($value)) {
                    $query->whereIn($column, $value, $boolean);
                } elseif ($normalizedOperator === 'between' && is_array($value) && count($value) === 2) {
                    $query->whereBetween($column, $value, $boolean);
                } else {
                    $query->where($column, $operator, $value, $boolean);
                }
            } else {
                $query->where($key, $condition);
            }
        }

        return $query;
    }

    /**
     * Shared query builder invoked by the query endpoint.
     */
    public function queryWithConditions(
        array $conditions = [],
        array $scopes = [],
        array $with = [],
        ?array $order = null,
        bool $paginate = false,
        int $perPage = 50
    ) {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        if (!empty($scopes)) {
            foreach ($scopes as $scope) {
                $query = $this->applyScopeFilter($query, $scope);
            }
        } else {
            $query = $this->applyScopeFilter($query, $this->getEffectiveScope());
        }

        $query = $this->applyConditions($query, $conditions);

        if ($order) {
            $column    = $order['column'] ?? 'created_at';
            $direction = $order['direction'] ?? 'desc';
            $query->orderBy($column, $direction);
        } else {
            $query->latest();
        }

        $query = $this->applyBudgetYearFilter($query, config('site.jolt_budget_year'));

        if ($paginate) {
            return $query->paginate(max($perPage, 1));
        }

        return $query->get();
    }

    protected function getTableColumns(): array
    {
        return Schema::getColumnListing($this->model->getTable());
    }

    /**
     * Apply personal scope filtering
     * Override in specific repositories if needed
     */
    protected function applyPersonalScope($query, $user)
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Apply departmental scope filtering
     * Override in specific repositories if needed
     */
    protected function applyDepartmentalScope($query, $user)
    {
        return $query->where('department_id', $user->department_id);
    }

    /**
     * Apply directorate scope filtering
     * Override in specific repositories if needed
     */
    protected function applyDirectorateScope($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('department_id', $user->department_id)
                ->orWhereHas('department', function ($subQ) use ($user) {
                    $subQ->where('parentId', $user->department_id);
                });
        });
    }

    public function dynamicCollection(
        array $conditions = [],
        array $scopes = [],
        array $with = [],
        bool $withTrashed = false,
        string $orderBy = 'created_at',
        string $orderDirection = 'desc',
    ): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();

        // Load relationships if provided
        if (!empty($with)) {
            $query->with($with);
        }

        // Include soft-deleted models if applicable
        if ($withTrashed && in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($this->model))) {
            $query->withTrashed();
        }

        // Apply scopes if any
        foreach ($scopes as $scope => $params) {
            if (is_int($scope)) {
                // Scope without parameters
                $query->$params();
            } elseif (is_array($params)) {
                $query->$scope(...$params);
            } else {
                $query->$scope($params);
            }
        }

        // Apply where conditions
        foreach ($conditions as $condition) {
            if (is_array($condition) && count($condition) === 3) {
                [$column, $operator, $value] = $condition;
                $query->where($column, $operator, $value);
            } elseif (is_array($condition)) {
                foreach ($condition as $column => $value) {
                    $query->where($column, '=', $value);
                }
            }
        }

        return $query->orderBy($orderBy, $orderDirection)->get();
    }

    public function instanceOfModel(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->newQuery();
    }
}

<?php

namespace App\Repositories;


use App\DTO\ProcessedIncomingData;
use App\Handlers\CodeGenerationErrorException;
use App\Handlers\DataNotFound;
use App\Handlers\RecordCreationUnsuccessful;
use App\Interfaces\IRepository;
use App\Models\Expenditure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

abstract class BaseRepository implements IRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    abstract public function parse(array $data): array;

    public function all()
    {
        return $this->model->latest()->get();
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

    public function getCollectionByColumn(string $column, mixed $value, string $operator = '=')
    {
        return $this->model->where($column, $operator, $value)->latest()->get();
    }

    public function instanceOfModel(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->newQuery();
    }
}

<?php

namespace App\Repositories;


use App\Handlers\CodeGenerationErrorException;
use App\Handlers\DataNotFound;
use App\Handlers\RecordCreationUnsuccessful;
use App\Interfaces\IRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

abstract class BaseRepository implements IRepository
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    abstract public function parse(array $data): array;

    public function all()
    {
        try {
            return $this->model->latest()->get();
        } catch (\Exception $e) {
            throw new \Exception('Error fetching collection: ' . $e->getMessage());
        }
    }

    public function find($id)
    {
        try {
            $record = $this->model->find($id);
            if (!$record) {
                throw new DataNotFound();
            }
            return $record;
        } catch (\Exception $e) {
            throw new \Exception('Error fetching record: ' . $e->getMessage());
        }
    }

    public function create(array $data)
    {
        try {
            $record = $this->model->create($this->parse($data));

            if (!$record) {
                throw new RecordCreationUnsuccessful();
            }

            return $record;
        } catch (\Exception $e) {
            throw new \Exception('Error creating record: ' . $e->getMessage());
        }
    }

    public function update(int $id, array $data)
    {
        try {
            $record = $this->find($id);

            if (!$record) {
                throw new DataNotFound();
            }

            $record->update($this->parse($data));

            return $record;
        } catch (\Exception $e) {
            throw new \Exception('Error updating record: ' . $e->getMessage());
        }
    }

    public function destroy($id): bool
    {
        try {
            $record = $this->find($id);

            if (!$record) {
                throw new DataNotFound();
            }

            return $record->delete();
        } catch (\Exception $e) {
            throw new \Exception('Error deleting record: ' . $e->getMessage());
        }
    }

    /**
     * @throws CodeGenerationErrorException
     * @throws \Exception
     */
    public function generate($column, $prefix): string
    {
        try {
            // Generate a random code with prefix
            $code = $prefix . random_int(10000, 99999);

            // Check if the code already exists in the specified column
            $record = $this->model->where($column, $code)->first();

            // If the code exists, recursively call the generate method to generate a new code
            if ($record) {
                return $this->generate($column, $prefix);
            }

            // If the code does not exist, return it
            return $code;
        } catch (QueryException $e) {
            throw new CodeGenerationErrorException('Error trying to generate code from this model!', 0, $e);
        } catch (\Exception $e) {
            throw new \Exception('Error generating code for this record: ' . $e->getMessage());
        }
    }

    public function getRecordByColumn(string $column, mixed $value, string $operator = '=')
    {
        try {
            $record = $this->model->where($column, $operator, $value)->first();

            if (!$record) {
                throw new DataNotFound();
            }

            return $record;
        } catch (\Exception $e) {
            throw new \Exception('Error fetching record: ' . $e->getMessage());
        }
    }

    public function getCollectionByColumn(string $column, mixed $value, string $operator = '=')
    {
        try {
            return $this->model->where($column, $operator, $value)->latest()->get();
        } catch (\Exception $e) {
            throw new \Exception('Error fetching collection: ' . $e->getMessage());
        }
    }

    public function instanceOfModel(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->newModelQuery();
    }
}

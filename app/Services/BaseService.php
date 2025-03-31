<?php

namespace App\Services;

use App\Handlers\CodeGenerationErrorException;
use App\Handlers\DataNotFound;
use App\Interfaces\IService;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class BaseService implements IService
{
    protected BaseRepository $repository;

    public function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws CodeGenerationErrorException
     */
    public function generate(string $column, string $prefix): string
    {
        return $this->repository->generate($column, $prefix);
    }

    public function indexFilter($id, $flag = "owner")
    {
        return match ($flag) {
            "department" => $this->repository->department($id),
            default => $this->repository->owner($id),
        };
    }

    public function getRecordByColumn(string $column, mixed $value, string $operator = '=')
    {
        return $this->repository->getRecordByColumn($column, $value, $operator);
    }

    public function getCollectionByColumn(string $column, mixed $value, string $operator = '=')
    {
        return $this->repository->getCollectionByColumn($column, $value, $operator);
    }

    public function index()
    {
        return $this->repository->all();
    }

    /**
     * @throws \Exception
     */
    public function store(array $data)
    {
        return $this->repository->create($data);
    }

    public function show(int $id)
    {
        return $this->repository->find($id);
    }

    /**
     * @throws DataNotFound
     */
    public function update(int $id, array $data, $parsed = true)
    {
        $record = $this->repository->find($id);

        if (!$record) {
            throw new ModelNotFoundException("Record not found");
        }

        return $this->repository->update($record->id, $data, $parsed);
    }

    /**
     * @throws \Exception
     */
    public function reform(int $id, array $data)
    {
        return $this->update($id, $data);
    }

    /**
     * @throws DataNotFound
     */
    public function destroy(int $id): bool
    {
        return $this->repository->destroy($id);
    }

    public function instanceQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->repository->instanceOfModel();
    }

    public function reliesOnStatus($departmentId, $status)
    {
        return $this->repository->basedOnStatus($departmentId, $status);
    }

    abstract public function rules($action = "store");
}

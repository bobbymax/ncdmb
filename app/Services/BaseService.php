<?php

namespace App\Services;

use App\Interfaces\IService;
use App\Repositories\BaseRepository;

abstract class BaseService implements IService
{
    protected BaseRepository $repository;

    public function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;
    }

    public function generate(string $column, string $prefix): string
    {
        try {
            return $this->repository->generate($column, $prefix);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    public function indexFilter($id, $flag = "owner")
    {
        if (!$id || $id < 1) return [];
        return match ($flag) {
            "department" => $this->repository->department($id),
            default => $this->repository->owner($id),
        };
    }

    public function getRecordByColumn(string $column, mixed $value, string $operator = '=')
    {
        try {
            return $this->repository->getRecordByColumn($column, $value, $operator);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getCollectionByColumn(string $column, mixed $value, string $operator = '=')
    {
        try {
            return $this->repository->getCollectionByColumn($column, $value, $operator);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function index()
    {
        try {
            return $this->repository->all();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function store(array $data)
    {
        try {
            return $this->repository->create($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function show(int $id)
    {
        try {
            return $this->repository->find($id);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function update(int $id, array $data, $parsed = true)
    {
        try {
            $record = $this->repository->find($id);
            if ($record) {
                $this->repository->update($record->id, $data, $parsed);
            }
            return $record;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    public function reform(int $id, array $data)
    {
        return $this->update($id, $data);
    }

    public function destroy(int $id): bool
    {
        try {
            return $this->repository->destroy($id);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function instanceQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->repository->instanceOfModel();
    }

    abstract public function rules($action = "store");
}

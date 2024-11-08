<?php

namespace App\Services;

use App\Interfaces\IService;

abstract class BaseService implements IService
{
    protected $repository;

    public function generate(string $column, string $prefix)
    {
        try {
            return $this->repository->generate($column, $prefix);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
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

    public function update(int $id, array $data)
    {
        try {
            return $this->repository->update($id, $data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function destroy(int $id)
    {
        try {
            return $this->repository->destroy($id);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    abstract public function rules($action = "store");
}

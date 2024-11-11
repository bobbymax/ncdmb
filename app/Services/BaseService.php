<?php

namespace App\Services;

use App\Interfaces\IService;
use App\Repositories\BaseRepository;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseService implements IService
{
    protected BaseRepository $repository;
    protected JsonResource $resource;

    public function __construct(BaseRepository $repository, JsonResource $resource)
    {
        $this->repository = $repository;
        $this->resource = $resource;
    }

    public function generate(string $column, string $prefix): string
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
            return $this->resource::collection($this->repository->all());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function store(array $data)
    {
        try {
            return new $this->resource($this->repository->create($data));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function show(int $id)
    {
        try {
            return new $this->resource($this->repository->find($id));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function update(int $id, array $data)
    {
        try {
            return new $this->resource($this->repository->update($id, $data));
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

    public function instanceQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->repository->instanceOfModel();
    }

    abstract public function rules($action = "store");
}

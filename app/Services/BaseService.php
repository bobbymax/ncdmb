<?php

namespace App\Services;

use App\DTO\ProcessedIncomingData;
use App\Handlers\CodeGenerationErrorException;
use App\Handlers\DataNotFound;
use App\Interfaces\IService;
use App\Models\Document;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class BaseService implements IService
{
    protected BaseRepository $repository;

    public function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;
    }

    public function resolveContent(array $data): array
    {
        return collect($data)->toArray();
    }

    public function getScope(): string
    {
        return $this->repository->rank();
    }

    public function bindRelatedDocuments(
        Document $document,
        mixed $resource,
        string $status = "processing"
    ) {
        if (!$resource) {
            return null;
        }
    }

    /**
     * @throws \Exception
     */
    public function documentProcessor(array $data, string $mode = "update")
    {
        return $this->store([
            ...$data,
            'mode' => $mode,
        ]);
    }

    public function resolveDocumentAmount(int $resourceId)
    {
        $raw = $this->repository->find($resourceId);

        if (!$raw) {
            return null;
        }

        return $this->updateDocumentAmount($raw);
    }

    public static function generatePaymentCode($prefix = "PAY"): string
    {
        // Timestamp (10 chars: Unix timestamp)
        $timestamp = now()->timestamp;

        // Random component (15 chars: uppercase alphanumeric)
        $random = Str::upper(Str::random(15));

        // Checksum (4 chars: from hash of prefix+timestamp+random)
        $checksum = strtoupper(substr(
            hash('crc32b', $prefix . $timestamp . $random),
            0,
            4
        ));

        // Total: 3 + 10 + 15 + 4 = 32 chars
        return $prefix . $timestamp . $random . $checksum;
    }

    protected function updateDocumentAmount(mixed $raw, string $column = "amount")
    {
        $raw->document->approved_amount = $raw[$column] ?? 0;
        $raw->document->save();

        return $raw;
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

    public function buildDocumentFromTemplate(array $data, bool $isUpdate = false)
    {
        return $data;
    }

    public function collection(
        array $conditions = [],
        array $scopes = [],
        array $with = [],
        bool $withTrashed = false,
        string $orderBy = 'created_at',
        string $orderDirection = 'desc'
    ): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->dynamicCollection(
            $conditions,
            $scopes,
            $with,
            $withTrashed,
            $orderBy,
            $orderDirection
        );
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

    public function whereIn(string $column, array $values)
    {
        return $this->repository->whereIn($column, $values);
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
    public function consolidate(ProcessedIncomingData $data): mixed
    {
        return $this->repository->consolidate($data);
    }

    public function compute($record)
    {
        return 0;
    }

    public function sumTotalAmount(int $id): int|float
    {
        $record = $this->repository->computeTotalAmount($id);
        return $this->compute($record);
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

    public function fetchDraftsInBatchQueue($departmentId, $status)
    {
        return $this->repository->inBatchQueue($departmentId, $status);
    }

    abstract public function rules($action = "store");
}

<?php

namespace App\Services;

use App\Repositories\LedgerRepository;
use Illuminate\Support\Facades\DB;

class LedgerService extends BaseService
{
    public function __construct(LedgerRepository $ledgerRepository)
    {
        parent::__construct($ledgerRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'code' => "required",
            'name' => "required|string|max:255",
            'description' => "nullable|string|max:255",
            'groups' => "required|array",
            'groups.*.id' => "required|exists:groups,id",
        ];
    }

    public function store(array $data)
    {
        return  DB::transaction(function () use ($data) {
            $ledger = parent::store($data);

            if (!$ledger) {
                return null;
            }

            $ids = array_column($data['groups'], 'id');
            $ledger->groups()->sync($ids);

            return $ledger;
        });
    }

    public function update(int $id, array $data, $parsed = true)
    {
        return DB::transaction(function () use ($id, $parsed, $data) {
            $ledger = parent::update($id, $data, $parsed);

            if (!$ledger) {
                return null;
            }

            $ids = array_column($data['groups'], 'id');
            $ledger->groups()->sync($ids);

            return $ledger;
        });
    }
}

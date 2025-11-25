<?php

namespace App\Imports;

use App\Interfaces\Importable;
use App\Models\ChartOfAccount;
use App\Repositories\ChartOfAccountRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChartOfAccountImport implements Importable
{
    public function __construct(
        protected ChartOfAccountRepository $chartOfAccountRepository
    ) {}

    public function import(array $rows): array
    {
        Log::info('Incoming data preview: ' . json_encode(array_slice($rows, 0, 3)));

        return DB::transaction(function () use ($rows) {
            $inserts = [];
            $skipped = 0;

            $accountCodes = array_column($rows, 'account_code');
            $existingAccounts = ChartOfAccount::whereIn('account_code', $accountCodes)->get()->keyBy('account_code');

            $seen = [];

            foreach ($rows as $row) {
                if (!is_array($row) || !isset($row['account_code'], $row['name'])) {
                    Log::warning("Skipping invalid item: " . json_encode($row));
                    $skipped++;
                    continue;
                }

                $accountCode = $row['account_code'];

                // Skip duplicates in input
                if (isset($seen[$accountCode])) {
                    Log::warning("Duplicate account_code in import payload: $accountCode");
                    $skipped++;
                    continue;
                }
                $seen[$accountCode] = true;

                // Skip existing in database
                if (isset($existingAccounts[$accountCode])) {
                    $skipped++;
                    continue;
                }

                unset($row['id'], $row['parent_id'], $row['deleted_at']);
                $row['parent_id'] = null;

                $row['account_code'] = $accountCode;

                $inserts[] = $row;
            }

            if (!empty($inserts)) {
                try {
                    $this->chartOfAccountRepository->insert($inserts);
                    Log::info("Inserted " . count($inserts) . " chart of account records.");
                } catch (\Exception $e) {
                    Log::error("Insert failed: " . $e->getMessage());
                    throw $e; // optional: rethrow to fail the job
                }
            } else {
                Log::info("No new accounts to insert.");
            }

            Log::info("inserted " . count($inserts) . " skipped " . $skipped . " chart of account records." . "total " . count($rows));

            return [
                'inserted' => count($inserts),
                'skipped' => $skipped,
                'total' => count($rows),
            ];
        });
    }
}

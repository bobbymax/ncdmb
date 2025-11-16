<?php

namespace App\Imports;

use App\Interfaces\Importable;
use App\Models\BudgetCode;
use App\Models\Department;
use App\Models\SubBudgetHead;
use App\Repositories\FundRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FundImport implements Importable
{
    public function __construct(protected FundRepository $fundRepository) {}

    public function import(array $rows): array
    {
        if (empty($rows)) {
            Log::error("FundImport: Data array is empty.");
            return ['inserted' => 0, 'total' => 0];
        }

        // Accept either a flat batch or a nested single-chunk payload
        // If nested (e.g., [ [row1, row2, ...] ]), unwrap one level.
        if (isset($rows[0]) && is_array($rows[0]) && array_keys($rows)[0] === 0 && isset($rows[0][0]) && is_array($rows[0][0])) {
            $rows = $rows[0];
        }

        $total = count($rows);
        $inserted = 0;

        return DB::transaction(function () use ($rows, $total, &$inserted) {
            $inserts = [];

            // Extract all IDs to reduce multiple queries
            $subBudgetHeadIds = array_column($rows, 'sub_budget_head_id');
            $departmentAbvs = array_map(fn($v) => strtoupper(trim($v ?? '')), array_column($rows, 'department'));
            $budgetCodes = array_map(fn($v) => strtoupper(trim($v ?? '')), array_column($rows, 'budget_code'));

            // Fetch required data in a single query
            $subBudgetHeads = SubBudgetHead::whereIn('id', $subBudgetHeadIds)->get()->keyBy('id');
            $departments = Department::whereIn('abv', $departmentAbvs)->get()->keyBy(fn ($d) => strtoupper($d->abv));
            $budgetCodeData = BudgetCode::whereIn('code', $budgetCodes)->get()->keyBy(fn ($b) => strtoupper($b->code));

            foreach ($rows as $item) {
                // Validate required fields
                if (!isset($item['sub_budget_head_id'], $item['department'], $item['budget_code'], $item['budget_year'], $item['approved'])) {
                    Log::warning("Skipping an invalid item: " . json_encode($item));
                    continue;
                }

                $subBudgetHead = $subBudgetHeads[$item['sub_budget_head_id']] ?? null;
                $departmentKey = strtoupper(trim($item['department'] ?? ''));
                $budgetCodeKey = strtoupper(trim($item['budget_code'] ?? ''));
                $department = $departments[$departmentKey] ?? null;
                $budgetCode = $budgetCodeData[$budgetCodeKey] ?? null;

                if ($subBudgetHead && $department && $budgetCode) {
                    $inserts[] = [
                        'sub_budget_head_id' => $subBudgetHead->id,
                        'department_id' => $department->id,
                        'budget_code_id' => $budgetCode->id,
                        'budget_year' => $item['budget_year'],
                        'total_approved_amount' => $item['approved'],
                        'type' => $this->getType($budgetCodeKey),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                } else {
                    if (!$subBudgetHead) {
                        Log::info('FundImport: Skipped (sub_budget_head not found): ' . ($item['sub_budget_head_id'] ?? 'null'));
                    }
                    if (!$department) {
                        Log::info('FundImport: Skipped (department not found): ' . ($item['department'] ?? 'null'));
                    }
                    if (!$budgetCode) {
                        Log::info('FundImport: Skipped (budget code not found): ' . ($item['budget_code'] ?? 'null'));
                    }
                }
            }

            if (!empty($inserts)) {
                try {
                    $this->fundRepository->insert($inserts);
                    $inserted = count($inserts);
                    Log::info("Inserted {$inserted} records into funds table.");
                } catch (\Exception $e) {
                    Log::error("Database Insert Error: " . $e->getMessage());
                }
            } else {
                Log::warning("No valid records found for insertion in this batch.");
            }

            return [
                'inserted' => $inserted,
                'total' => $total,
            ];
        });
    }

    protected function getType($budget_code): string
    {
        $letter = $budget_code[0];

        return match ($letter) {
            'C', 'c' => 'capital',
            'R', 'r' => 'recurrent',
            default => 'personnel'
        };
    }
}

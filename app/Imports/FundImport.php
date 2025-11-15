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
            Log::error("handleFundUpload: Data array is empty.");
            return [];
        }

        return DB::transaction(function () use ($rows) {
            foreach ($rows as $chunk) {
                $inserts = [];

                // Extract all IDs to reduce multiple queries
                $subBudgetHeadIds = array_column($chunk, 'sub_budget_head_id');
                $departmentAbvs = array_column($chunk, 'department');
                $budgetCodes = array_column($chunk, 'budget_code');

                // Fetch required data in a single query
                $subBudgetHeads = SubBudgetHead::whereIn('id', $subBudgetHeadIds)->get()->keyBy('id');
                $departments = Department::whereIn('abv', $departmentAbvs)->get()->keyBy('abv');
                $budgetCodeData = BudgetCode::whereIn('code', $budgetCodes)->get()->keyBy('code');

                foreach ($chunk as $item) {
                    if (empty($item) || !isset($item['budget_year'], $item['approved'])) {
                        Log::warning("Skipping an invalid item: " . json_encode($item));
                        continue;
                    }

                    $subBudgetHead = $subBudgetHeads[$item['sub_budget_head_id']] ?? null;
                    $department = $departments[$item['department']] ?? null;
                    $budgetCode = $budgetCodeData[$item['budget_code']] ?? null;

                    if ($subBudgetHead && $department && $budgetCode) {
                        $inserts[] = [
                            'sub_budget_head_id' => $subBudgetHead->id,
                            'department_id' => $department->id,
                            'budget_code_id' => $budgetCode->id,
                            'budget_year' => $item['budget_year'],
                            'total_approved_amount' => $item['approved'],
                            'type' => $this->getType($item['budget_code']),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                if (!empty($inserts)) {
                    try {
                        DB::transaction(function () use ($inserts) {
                            $this->fundRepository->insert($inserts);
                        });

                        Log::info("Inserted " . count($inserts) . " records into funds table.");
                    } catch (\Exception $e) {
                        Log::error("Database Insert Error: " . $e->getMessage());
                    }
                } else {
                    Log::warning("No valid records found for insertion in this chunk.");
                }
            }

            return [
                'inserted' => count($inserts),
                'total' => count($rows)
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

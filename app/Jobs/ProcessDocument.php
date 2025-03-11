<?php

namespace App\Jobs;

use App\Models\BudgetCode;
use App\Models\Department;
use App\Models\SubBudgetHead;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;

    public function __construct(public $resource, public $data = []) {}

    public function handle(): bool
    {
        Log::info("Job started for resource: " . $this->resource);

        if (empty($this->data)) {
            Log::error("Job failed: No data provided.");
            return false;
        }

        return match ($this->resource) {
            'funds' => $this->handleFundUpload($this->data),
            default => false,
        };
    }

    public function handleFundUpload(array $data): bool
    {
        if (empty($data)) {
            Log::error("handleFundUpload: Data array is empty.");
            return false;
        }

        // Chunk the uploaded data for batch processing
        $chunks = array_chunk($data, 1000);

        foreach ($chunks as $chunk) {
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
                        // 'id' => $item['id'], // REMOVE if ID is AUTO_INCREMENT
                        'sub_budget_head_id' => $subBudgetHead->id,
                        'department_id' => $department->id,
                        'budget_code_id' => $budgetCode->id,
                        'budget_year' => $item['budget_year'],
                        'total_approved_amount' => $item['approved'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($inserts)) {
                try {
                    DB::transaction(function () use ($inserts) {
                        DB::table('funds')->insert($inserts);
                    });

                    Log::info("Inserted " . count($inserts) . " records into funds table.");
                } catch (\Exception $e) {
                    Log::error("Database Insert Error: " . $e->getMessage());
                }
            } else {
                Log::warning("No valid records found for insertion in this chunk.");
            }
        }

        return true;
    }

    public function failed(\Exception $exception): void
    {
        Log::error("Job Failed: " . $exception->getMessage());
    }
}

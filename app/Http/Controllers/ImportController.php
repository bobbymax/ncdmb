<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDocument;
use App\Models\BudgetCode;
use App\Models\Department;
use App\Models\SubBudgetHead;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ImportController extends Controller
{
    use ApiResponse;
    public function getResource(Request $request, $resource): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Please fix the following errors', 500);
        }

        $def = $this->handleFundUpload($request->data);

        if (!$def) {
            return $this->error("Invalid data", '', 500);
        }

//        ProcessDocument::dispatch($resource, $request->data)
//            ->onQueue('high_priority')
//            ->delay(now()->addSeconds(5));

//        ProcessDocument::dispatch($resource, $request->data);

        return $this->success("Sorting");
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

    protected function getType($budget_code): string
    {
        $letter = $budget_code[0];

        return match ($letter) {
            'C', 'c' => 'capital',
            'R', 'r' => 'recurrent',
            default => 'personnel'
        };
    }

    public function handleImport(array $data): string
    {
        return "Processing Started";
    }
}

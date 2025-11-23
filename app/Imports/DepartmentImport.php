<?php

namespace App\Imports;

use App\Interfaces\Importable;
use App\Models\Department;
use App\Repositories\DepartmentRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DepartmentImport implements Importable
{
    public function __construct(
        protected DepartmentRepository $departmentRepository
    ) {}

    public function import(array $rows): array
    {
        if (empty($rows)) {
            Log::error("DepartmentImport: Data array is empty.");
            return ['inserted' => 0, 'total' => 0, 'skipped' => 0];
        }

        // Accept either a flat batch or a nested single-chunk payload
        // If nested (e.g., [ [row1, row2, ...] ]), unwrap one level.
        if (isset($rows[0]) && is_array($rows[0]) && array_keys($rows)[0] === 0 && isset($rows[0][0]) && is_array($rows[0][0])) {
            $rows = $rows[0];
        }

        return DB::transaction(function () use ($rows) {
            $total = count($rows);
            $inserted = 0;
            $skipped = 0;
            $inserts = [];

            // Extract all identifiers for bulk lookups
            $abvs = array_map(fn($v) => strtoupper(trim($v ?? '')), array_column($rows, 'abv'));
            $parentAbvs = array_filter(array_map(fn($v) => strtoupper(trim($v ?? '')), array_column($rows, 'parent_abv')));

            // Bulk fetch existing departments to check for duplicates
            $existingDepartments = Department::whereIn('abv', $abvs)
                ->orWhereIn('label', array_map(fn($abv) => Str::slug($abv), $abvs))
                ->get()
                ->keyBy('abv');

            // Bulk fetch parent departments by abv
            $parentDepartments = collect();
            if (!empty($parentAbvs)) {
                $parentDepartments = Department::whereIn('abv', $parentAbvs)->get()->keyBy(function ($dept) {
                    return strtoupper($dept->abv);
                });
            }

            foreach ($rows as $item) {
                // Validate required fields
                if (!isset($item['name'], $item['abv'])) {
                    Log::warning("DepartmentImport: Skipping invalid item (missing name/abv): " . json_encode($item));
                    $skipped++;
                    continue;
                }

                $abv = strtoupper(trim($item['abv']));
                $name = trim($item['name'] ?? '');
                $label = !empty($item['label']) ? trim($item['label']) : Str::slug($name);

                // Check for duplicates
                if (isset($existingDepartments[$abv])) {
                    Log::info("DepartmentImport: Skipped (already exists): {$abv}");
                    $skipped++;
                    continue;
                }

                // Resolve parent department using parent_abv
                $parentId = null;
                if (!empty($item['parent_abv'])) {
                    $parentAbv = strtoupper(trim($item['parent_abv']));
                    $parent = $parentDepartments->firstWhere('abv', $parentAbv);
                    if ($parent) {
                        $parentId = $parent->id;
                    } else {
                        Log::warning("DepartmentImport: Parent department not found for abv: {$parentAbv}");
                    }
                }

                // Get fields from Excel as-is
                $type = !empty($item['type']) ? trim($item['type']) : 'department';
                $isSovereign = isset($item['is_sovereign']) ? (bool) $item['is_sovereign'] : false;

                // Validate type
                $validTypes = ['directorate', 'division', 'department', 'unit'];
                if (!in_array($type, $validTypes)) {
                    Log::warning("DepartmentImport: Invalid type '{$type}', defaulting to 'department'");
                    $type = 'department';
                }

                $inserts[] = [
                    'name' => $name,
                    'label' => $label,
                    'abv' => $abv,
                    'type' => $type,
                    'parent_id' => $parentId,
                    'is_sovereign' => $isSovereign,
                    'signatory_staff_id' => null,
                    'alternate_signatory_staff_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Track this as existing to avoid duplicates in the same batch
                $existingDepartments->put($abv, (object)['abv' => $abv]);
            }

            if (!empty($inserts)) {
                try {
                    $this->departmentRepository->insert($inserts);
                    $inserted = count($inserts);
                    Log::info("DepartmentImport: Inserted {$inserted} records into departments table.");
                } catch (\Exception $e) {
                    Log::error("DepartmentImport: Database Insert Error: " . $e->getMessage());
                    throw $e;
                }
            } else {
                Log::warning("DepartmentImport: No valid records found for insertion in this batch.");
            }

            return [
                'inserted' => $inserted,
                'skipped' => $skipped,
                'total' => $total,
            ];
        });
    }
}


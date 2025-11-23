<?php

namespace App\Imports;

use App\Interfaces\Importable;
use App\Models\Group;
use App\Repositories\GroupRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GroupImport implements Importable
{
    public function __construct(
        protected GroupRepository $groupRepository
    ) {}

    public function import(array $rows): array
    {
        if (empty($rows)) {
            Log::error("GroupImport: Data array is empty.");
            return ['inserted' => 0, 'total' => 0, 'skipped' => 0];
        }

        // Accept either a flat batch or a nested single-chunk payload
        if (isset($rows[0]) && is_array($rows[0]) && array_keys($rows)[0] === 0 && isset($rows[0][0]) && is_array($rows[0][0])) {
            $rows = $rows[0];
        }

        return DB::transaction(function () use ($rows) {
            $total = count($rows);
            $inserted = 0;
            $skipped = 0;
            $inserts = [];

            // Bulk fetch existing groups to check for duplicates
            $groupNames = array_map(fn($v) => trim($v ?? ''), array_column($rows, 'name'));
            $existingGroups = Group::whereIn('label', array_map(fn($name) => Str::slug($name), $groupNames))->get()->keyBy('label');

            foreach ($rows as $item) {
                // Validate required fields
                if (!isset($item['name'])) {
                    Log::warning("GroupImport: Skipping invalid item (missing name): " . json_encode($item));
                    $skipped++;
                    continue;
                }

                $name = trim($item['name']);
                $label = Str::slug($name);

                // Check for duplicates
                if (isset($existingGroups[$label])) {
                    Log::info("GroupImport: Skipped (already exists): {$name}");
                    $skipped++;
                    continue;
                }

                $inserts[] = [
                    'name' => $name,
                    'label' => $label,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Track this as existing to avoid duplicates in the same batch
                $existingGroups->put($label, (object)['label' => $label]);
            }

            if (!empty($inserts)) {
                try {
                    $this->groupRepository->insert($inserts);
                    $inserted = count($inserts);
                    Log::info("GroupImport: Inserted {$inserted} records into groups table.");
                } catch (\Exception $e) {
                    Log::error("GroupImport: Database Insert Error: " . $e->getMessage());
                    throw $e;
                }
            } else {
                Log::warning("GroupImport: No valid records found for insertion in this batch.");
            }

            return [
                'inserted' => $inserted,
                'skipped' => $skipped,
                'total' => $total,
            ];
        });
    }
}


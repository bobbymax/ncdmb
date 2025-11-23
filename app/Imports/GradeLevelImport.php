<?php

namespace App\Imports;

use App\Interfaces\Importable;
use App\Models\Carder;
use App\Models\GradeLevel;
use App\Repositories\GradeLevelRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GradeLevelImport implements Importable
{
    public function __construct(
        protected GradeLevelRepository $gradeLevelRepository
    ) {}

    public function import(array $rows): array
    {
        if (empty($rows)) {
            Log::error("GradeLevelImport: Data array is empty.");
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

            // Extract carder identifiers
            $carderLabels = array_filter(array_map(fn($v) => trim($v ?? ''), array_column($rows, 'carder_label')));
            $carderNames = array_filter(array_map(fn($v) => trim($v ?? ''), array_column($rows, 'carder_name')));

            // Bulk fetch carders
            $carders = collect();
            if (!empty($carderLabels)) {
                $carders = $carders->merge(Carder::whereIn('label', $carderLabels)->get());
            }
            if (!empty($carderNames)) {
                $carders = $carders->merge(Carder::whereIn('name', $carderNames)->get());
            }
            $carders = $carders->keyBy('label')->merge($carders->keyBy('name'));

            // Bulk fetch existing grade levels to check for duplicates
            $gradeLevelKeys = array_map(fn($v) => strtoupper(trim($v ?? '')), array_column($rows, 'key'));
            $existingGradeLevels = GradeLevel::whereIn('key', $gradeLevelKeys)->get()->keyBy('key');

            foreach ($rows as $item) {
                // Validate required fields
                if (!isset($item['key'], $item['name'])) {
                    Log::warning("GradeLevelImport: Skipping invalid item (missing key/name): " . json_encode($item));
                    $skipped++;
                    continue;
                }

                $key = strtoupper(trim($item['key']));
                $name = trim($item['name']);

                // Check for duplicates
                if (isset($existingGradeLevels[$key])) {
                    Log::info("GradeLevelImport: Skipped (already exists): {$key}");
                    $skipped++;
                    continue;
                }

                // Resolve carder
                $carderId = 0;
                if (!empty($item['carder_label'])) {
                    $carderLabel = trim($item['carder_label']);
                    $carder = $carders->firstWhere('label', $carderLabel);
                    if ($carder) {
                        $carderId = $carder->id;
                    } else {
                        Log::warning("GradeLevelImport: Carder not found for label: {$carderLabel}");
                    }
                } elseif (!empty($item['carder_name'])) {
                    $carderName = trim($item['carder_name']);
                    $carder = $carders->firstWhere('name', $carderName);
                    if ($carder) {
                        $carderId = $carder->id;
                    } else {
                        Log::warning("GradeLevelImport: Carder not found for name: {$carderName}");
                    }
                } elseif (!empty($item['carder_id'])) {
                    $carderId = (int) $item['carder_id'];
                }

                // Validate type
                $type = $item['type'] ?? 'board';
                $validTypes = ['system', 'board'];
                if (!in_array($type, $validTypes)) {
                    Log::warning("GradeLevelImport: Invalid type '{$type}', defaulting to 'board'");
                    $type = 'board';
                }

                // Validate rank (0-13)
                $rank = isset($item['rank']) ? (int) $item['rank'] : 13;
                if ($rank < 0 || $rank > 13) {
                    Log::warning("GradeLevelImport: Invalid rank '{$rank}', defaulting to 13");
                    $rank = 13;
                }

                $inserts[] = [
                    'key' => $key,
                    'name' => $name,
                    'type' => $type,
                    'carder_id' => $carderId,
                    'rank' => $rank,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Track this as existing to avoid duplicates in the same batch
                $existingGradeLevels->put($key, (object)['key' => $key]);
            }

            if (!empty($inserts)) {
                try {
                    $this->gradeLevelRepository->insert($inserts);
                    $inserted = count($inserts);
                    Log::info("GradeLevelImport: Inserted {$inserted} records into grade_levels table.");
                } catch (\Exception $e) {
                    Log::error("GradeLevelImport: Database Insert Error: " . $e->getMessage());
                    throw $e;
                }
            } else {
                Log::warning("GradeLevelImport: No valid records found for insertion in this batch.");
            }

            return [
                'inserted' => $inserted,
                'skipped' => $skipped,
                'total' => $total,
            ];
        });
    }
}


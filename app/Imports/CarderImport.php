<?php

namespace App\Imports;

use App\Interfaces\Importable;
use App\Models\Carder;
use App\Models\Group;
use App\Repositories\CarderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CarderImport implements Importable
{
    public function __construct(
        protected CarderRepository $carderRepository
    ) {}

    public function import(array $rows): array
    {
        if (empty($rows)) {
            Log::error("CarderImport: Data array is empty.");
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
            $groupRelations = []; // Store carder_id => group_ids for later attachment

            // Extract group identifiers
            $groupLabels = [];
            $groupNames = [];
            foreach ($rows as $item) {
                if (!empty($item['groups'])) {
                    if (is_array($item['groups'])) {
                        foreach ($item['groups'] as $group) {
                            if (is_string($group)) {
                                // Could be label or name
                                $groupLabels[] = Str::slug($group);
                                $groupNames[] = trim($group);
                            } elseif (is_array($group) && isset($group['label'])) {
                                $groupLabels[] = $group['label'];
                            } elseif (is_array($group) && isset($group['name'])) {
                                $groupNames[] = trim($group['name']);
                            }
                        }
                    }
                }
            }

            // Bulk fetch groups
            $groups = collect();
            if (!empty($groupLabels)) {
                $groups = $groups->merge(Group::whereIn('label', array_unique($groupLabels))->get());
            }
            if (!empty($groupNames)) {
                $groups = $groups->merge(Group::whereIn('name', array_unique($groupNames))->get());
            }
            $groups = $groups->keyBy('label')->merge($groups->keyBy('name'));

            // Bulk fetch existing carders to check for duplicates
            $carderNames = array_map(fn($v) => trim($v ?? ''), array_column($rows, 'name'));
            $existingCarders = Carder::whereIn('label', array_map(fn($name) => Str::slug($name), $carderNames))->get()->keyBy('label');

            foreach ($rows as $index => $item) {
                // Validate required fields
                if (!isset($item['name'])) {
                    Log::warning("CarderImport: Skipping invalid item (missing name): " . json_encode($item));
                    $skipped++;
                    continue;
                }

                $name = trim($item['name']);
                $label = Str::slug($name);

                // Check for duplicates
                if (isset($existingCarders[$label])) {
                    Log::info("CarderImport: Skipped (already exists): {$name}");
                    $skipped++;
                    continue;
                }

                $inserts[] = [
                    'name' => $name,
                    'label' => $label,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Store group relationships for this carder (will be attached after insert)
                if (!empty($item['groups']) && is_array($item['groups'])) {
                    $groupIds = [];
                    foreach ($item['groups'] as $group) {
                        $groupLabel = null;
                        $groupName = null;
                        
                        if (is_string($group)) {
                            $groupLabel = Str::slug($group);
                            $groupName = trim($group);
                        } elseif (is_array($group)) {
                            $groupLabel = isset($group['label']) ? $group['label'] : null;
                            $groupName = isset($group['name']) ? trim($group['name']) : null;
                        }

                        $foundGroup = null;
                        if ($groupLabel && isset($groups[$groupLabel])) {
                            $foundGroup = $groups[$groupLabel];
                        } elseif ($groupName && isset($groups[$groupName])) {
                            $foundGroup = $groups[$groupName];
                        }

                        if ($foundGroup) {
                            $groupIds[] = $foundGroup->id;
                        } else {
                            Log::warning("CarderImport: Group not found for carder '{$name}': " . ($groupLabel ?? $groupName ?? 'unknown'));
                        }
                    }
                    
                    if (!empty($groupIds)) {
                        $groupRelations[count($inserts) - 1] = $groupIds; // Store index for later
                    }
                }

                // Track this as existing to avoid duplicates in the same batch
                $existingCarders->put($label, (object)['label' => $label]);
            }

            if (!empty($inserts)) {
                try {
                    $this->carderRepository->insert($inserts);
                    $inserted = count($inserts);
                    Log::info("CarderImport: Inserted {$inserted} records into carders table.");

                    // Attach groups to carders
                    if (!empty($groupRelations)) {
                        $insertedCarders = Carder::whereIn('label', array_map(fn($insert) => $insert['label'], $inserts))
                            ->get()
                            ->keyBy('label');

                        foreach ($groupRelations as $insertIndex => $groupIds) {
                            $carderLabel = $inserts[$insertIndex]['label'];
                            $carder = $insertedCarders[$carderLabel] ?? null;
                            
                            if ($carder) {
                                foreach ($groupIds as $groupId) {
                                    if (!$carder->groups()->where('groups.id', $groupId)->exists()) {
                                        $carder->groups()->attach($groupId);
                                    }
                                }
                                Log::info("CarderImport: Attached " . count($groupIds) . " groups to carder '{$carder->name}'");
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("CarderImport: Database Insert Error: " . $e->getMessage());
                    throw $e;
                }
            } else {
                Log::warning("CarderImport: No valid records found for insertion in this batch.");
            }

            return [
                'inserted' => $inserted,
                'skipped' => $skipped,
                'total' => $total,
            ];
        });
    }
}


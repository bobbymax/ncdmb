<?php

namespace App\Imports;

use App\Interfaces\Importable;
use App\Models\Department;
use App\Models\Role;
use App\Repositories\RoleRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RoleImport implements Importable
{
    public function __construct(
        protected RoleRepository $roleRepository
    ) {}

    public function import(array $rows): array
    {
        if (empty($rows)) {
            Log::error("RoleImport: Data array is empty.");
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

            // Extract department identifiers
            $deptAbvs = array_filter(array_map(fn($v) => strtoupper(trim($v ?? '')), array_column($rows, 'department_abv')));
            $deptNames = array_filter(array_map(fn($v) => trim($v ?? ''), array_column($rows, 'department_name')));

            // Bulk fetch departments
            $departments = collect();
            if (!empty($deptAbvs)) {
                $departments = $departments->merge(Department::whereIn('abv', $deptAbvs)->get());
            }
            if (!empty($deptNames)) {
                $departments = $departments->merge(Department::whereIn('name', $deptNames)->get());
            }
            $departments = $departments->keyBy(function ($dept) {
                return strtoupper($dept->abv);
            })->merge($departments->keyBy('name'));

            // Bulk fetch existing roles to check for duplicates
            $roleNames = array_map(fn($v) => trim($v ?? ''), array_column($rows, 'name'));
            $existingRoles = Role::whereIn('slug', array_map(fn($name) => Str::slug($name), $roleNames))->get()->keyBy('slug');

            foreach ($rows as $item) {
                // Validate required fields
                if (!isset($item['name'])) {
                    Log::warning("RoleImport: Skipping invalid item (missing name): " . json_encode($item));
                    $skipped++;
                    continue;
                }

                $name = trim($item['name']);
                $slug = Str::slug($name);

                // Check for duplicates
                if (isset($existingRoles[$slug])) {
                    Log::info("RoleImport: Skipped (already exists): {$name}");
                    $skipped++;
                    continue;
                }

                // Resolve department
                $departmentId = null;
                if (!empty($item['department_abv'])) {
                    $deptAbv = strtoupper(trim($item['department_abv']));
                    $department = $departments->firstWhere('abv', $deptAbv);
                    if ($department) {
                        $departmentId = $department->id;
                    } else {
                        Log::warning("RoleImport: Department not found for abv: {$deptAbv}");
                    }
                } elseif (!empty($item['department_name'])) {
                    $deptName = trim($item['department_name']);
                    $department = $departments->firstWhere('name', $deptName);
                    if ($department) {
                        $departmentId = $department->id;
                    } else {
                        Log::warning("RoleImport: Department not found for name: {$deptName}");
                    }
                } elseif (!empty($item['department_id'])) {
                    $departmentId = (int) $item['department_id'];
                }

                if (!$departmentId) {
                    Log::warning("RoleImport: Skipped (department required): {$name}");
                    $skipped++;
                    continue;
                }

                // Validate access_level
                $accessLevel = $item['access_level'] ?? 'basic';
                $validAccessLevels = ['basic', 'operative', 'control', 'command', 'sovereign', 'system'];
                if (!in_array($accessLevel, $validAccessLevels)) {
                    Log::warning("RoleImport: Invalid access_level '{$accessLevel}', defaulting to 'basic'");
                    $accessLevel = 'basic';
                }

                $inserts[] = [
                    'name' => $name,
                    'slug' => $slug,
                    'department_id' => $departmentId,
                    'slots' => isset($item['slots']) ? (int) $item['slots'] : 1,
                    'access_level' => $accessLevel,
                    'issued_date' => !empty($item['issued_date']) ? $item['issued_date'] : null,
                    'expired_date' => !empty($item['expired_date']) ? $item['expired_date'] : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Track this as existing to avoid duplicates in the same batch
                $existingRoles->put($slug, (object)['slug' => $slug]);
            }

            if (!empty($inserts)) {
                try {
                    $this->roleRepository->insert($inserts);
                    $inserted = count($inserts);
                    Log::info("RoleImport: Inserted {$inserted} records into roles table.");
                } catch (\Exception $e) {
                    Log::error("RoleImport: Database Insert Error: " . $e->getMessage());
                    throw $e;
                }
            } else {
                Log::warning("RoleImport: No valid records found for insertion in this batch.");
            }

            return [
                'inserted' => $inserted,
                'skipped' => $skipped,
                'total' => $total,
            ];
        });
    }
}


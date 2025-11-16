<?php

namespace App\Imports;

use App\Interfaces\Importable;
use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserImport implements Importable
{
    public function __construct(protected UserRepository $userRepository) {}

    public function import(array $rows): array
    {
        if (empty($rows)) {
            Log::error("UserImport: Data array is empty.");
            return [];
        }

        return DB::transaction(function () use ($rows) {
            $totalRows = 0;
            $totalInserted = 0;

            foreach ($rows as $chunk) {
                if (!is_array($chunk) || !isset($chunk[0])) {
                    $chunk = [$chunk]; // normalize single row into a chunk
                }

                $totalRows += count($chunk);
                $inserts = [];

                // Extract Required Data in a single query
                $gradeLevelKeys = array_column($chunk, 'rank');
                $userEmails = array_column($chunk, 'email');
                $deptAbvs = array_map(fn($v) => strtoupper(trim($v ?? '')), array_column($chunk, 'dept'));

                $gradeLevels = GradeLevel::whereIn('key', $gradeLevelKeys)->get()->keyBy('key');
                $existingUsers = User::whereIn('email', $userEmails)->get()->keyBy('email');
                $departments = Department::whereIn('abv', $deptAbvs)->get()->keyBy('abv');
                $role = Role::where('slug', 'staff')->first();
                $location = Location::first();

                // Diagnostics
                $requestedDepts = array_values(array_unique($deptAbvs));
                $fetchedDeptKeys = $departments->keys()->all();
                $missingDepts = array_values(array_diff($requestedDepts, $fetchedDeptKeys));
                if (!empty($missingDepts)) {
                    Log::warning('UserImport: Missing departments (abv) in chunk: ' . implode(', ', $missingDepts));
                }

                $requestedRanks = array_values(array_unique($gradeLevelKeys));
                $fetchedRankKeys = $gradeLevels->keys()->all();
                $missingRanks = array_values(array_diff($requestedRanks, $fetchedRankKeys));
                if (!empty($missingRanks)) {
                    Log::warning('UserImport: Missing grade levels (key) in chunk: ' . implode(', ', $missingRanks));
                }

                if (!$role) {
                    Log::warning('UserImport: Role with slug=staff not found.');
                }
                if (!$location) {
                    Log::warning('UserImport: No Location found.');
                }

                foreach ($chunk as $item) {
                    if (empty($item) || !isset($item['email'], $item['rank'], $item['dept'])) {
                        Log::warning("Skipping an invalid item: " . json_encode($item));
                        continue;
                    }

                    $deptKey = strtoupper(trim($item['dept'] ?? ''));

                    $user = $existingUsers[$item['email']] ?? null;
                    $department = $departments[$deptKey] ?? null;
                    $rankKey = strtoupper(trim($item['rank'] ?? ''));
                    $rank = $gradeLevels[$rankKey] ?? null;

                    if ($user) {
                        Log::info('UserImport: Skipped (user exists): ' . $item['email']);
                        continue;
                    }

                    if ($department && $rank && $location && $role) {
                        $local = explode('@', $item['email'])[0] ?? '';
                        [$firstPart, $secondPart] = array_pad(explode('.', $local, 2), 2, '');
                        $firstnameGuess = $firstPart !== '' ? strtoupper($firstPart) : '';
                        $surnameGuess = $secondPart !== '' ? strtoupper($secondPart) : '';

                        $firstname = ($item['firstname'] ?? '') !== '' ? $item['firstname'] : $firstnameGuess;
                        $surname = ($item['surname'] ?? '') !== '' ? $item['surname'] : $surnameGuess;

                        $inserts[] = [
                            'email' => $item['email'],
                            'department_id' => $department->id,
                            'staff_no' => $item['staff_no'] ?? null,
                            'grade_level_id' => $rank->id,
                            'role_id' => $role->id,
                            'location_id' => $location->id,
                            'date_joined' => now(),
                            'password' => Hash::make(trim($firstname . "." . $surname)),
                            'firstname' => $firstname,
                            'middlename' => null,
                            'surname' => $surname,
                            'is_admin' => false,
                            'change_password' => true,
                            'type' => $item['type'] ?? "permanent",
                            'two_factor_secret' => null,
                            'two_factor_recovery_codes' => null,
                            'two_factor_confirmed_at' => null,
                            'two_factor_enabled' => false,
                            'status' => 'available',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    } else {
                        if (!$department) {
                            Log::info('UserImport: Skipped (department not found): ' . $deptKey . ' for ' . $item['email']);
                        }
                        if (!$rank) {
                            Log::info('UserImport: Skipped (rank not found): ' . ($item['rank'] ?? 'null') . ' for ' . $item['email']);
                        }
                        if (!$role || !$location) {
                            Log::info('UserImport: Skipped (role/location missing) for ' . $item['email']);
                        }
                    }
                }

                if (!empty($inserts)) {
                    try {
                        $this->userRepository->insert($inserts);
                        $totalInserted += count($inserts);
                    } catch (\Exception $e) {
                        Log::error("Database Insert Error: " . $e->getMessage());
                    }
                } else {
                    Log::warning("UserImport: No valid records found for insertion in this chunk. Example: " . json_encode(array_slice($chunk, 0, 1)));
                }
            }

            return [
                'inserted' => $totalInserted,
                'total' => $totalRows,
            ];
        });
    }
}

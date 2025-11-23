<?php

namespace App\Imports;

use App\Interfaces\Importable;
use App\Models\Group;
use App\Models\Page;
use App\Models\Role;
use App\Repositories\PageRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PageImport implements Importable
{
    public function __construct(
        protected PageRepository $pageRepository
    ) {}

    public function import(array $rows): array
    {
        if (empty($rows)) {
            Log::error("PageImport: Data array is empty.");
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
            $parentRelations = []; // Store [child_label => parent_label] for pages that need parent resolution after insert

            // Step 1: Get the Super Administrator role and Enterprise Administrators group
            $superAdminRole = Role::where('slug', 'super-administrator')->first();
            if (!$superAdminRole) {
                Log::warning("PageImport: Super Administrator role not found (slug: super-administrator)");
            } else {
                Log::info("PageImport: Found Super Administrator role (ID: {$superAdminRole->id})");
            }

            $enterpriseAdminGroup = Group::where('label', 'enterprise-administrators')->first();
            if (!$enterpriseAdminGroup) {
                Log::warning("PageImport: Enterprise Administrators group not found (label: enterprise-administrators)");
            } else {
                Log::info("PageImport: Found Enterprise Administrators group (ID: {$enterpriseAdminGroup->id})");
            }

            // Step 2: Build a map of pages in the current batch (by label) for parent resolution
            $batchPagesMap = [];
            foreach ($rows as $item) {
                if (!empty($item['name'])) {
                    $itemLabel = !empty($item['label']) ? trim($item['label']) : Str::slug(trim($item['name']));
                    $batchPagesMap[$itemLabel] = $item;
                }
            }
            Log::info("PageImport: Processing " . count($batchPagesMap) . " pages in batch");

            // Step 3: Extract all parent labels that need to be resolved
            $allParentLabels = [];
            foreach ($rows as $item) {
                if (!empty($item['parent']) && !empty($item['type']) && trim($item['type']) !== 'app') {
                    $allParentLabels[] = trim($item['parent']);
                }
            }
            $allParentLabels = array_unique($allParentLabels);

            // Step 4: Bulk fetch existing parent pages from database
            $parentPagesFromDb = collect();
            if (!empty($allParentLabels)) {
                Log::info("PageImport: Looking for parent pages in database: " . implode(', ', $allParentLabels));
                $parentPagesFromDb = Page::whereIn('label', $allParentLabels)->get()->keyBy('label');
                Log::info("PageImport: Found " . $parentPagesFromDb->count() . " parent pages in database");
            }

            // Step 5: Bulk fetch existing pages to check for duplicates
            $existingPageLabels = [];
            foreach ($rows as $item) {
                if (!empty($item['name'])) {
                    $itemLabel = !empty($item['label']) ? trim($item['label']) : Str::slug(trim($item['name']));
                    $existingPageLabels[] = $itemLabel;
                }
            }
            $existingPages = Page::whereIn('label', array_unique($existingPageLabels))->get()->keyBy('label');

            // Step 6: Process each row and prepare inserts
            foreach ($rows as $index => $item) {
                // Validate required field: name
                if (!isset($item['name']) || empty(trim($item['name']))) {
                    Log::warning("PageImport: Skipping row " . ($index + 1) . " - missing name: " . json_encode($item));
                    $skipped++;
                    continue;
                }

                // Extract and clean values from Excel columns
                $name = trim($item['name']);
                $label = !empty($item['label']) ? trim($item['label']) : Str::slug($name);
                $icon = !empty($item['icon']) ? trim($item['icon']) : null;
                $type = !empty($item['type']) ? trim($item['type']) : 'index';
                $parent = !empty($item['parent']) ? trim($item['parent']) : null;

                // Validate type
                $validTypes = ['app', 'index', 'view', 'form', 'external', 'dashboard', 'report'];
                if (!in_array($type, $validTypes)) {
                    Log::warning("PageImport: Invalid type '{$type}' for page '{$name}', defaulting to 'index'");
                    $type = 'index';
                }

                // Check for duplicates by label
                if (isset($existingPages[$label])) {
                    Log::info("PageImport: Skipped page '{$name}' (label '{$label}' already exists)");
                    $skipped++;
                    continue;
                }

                // Calculate initial path (will be updated after parent resolution if needed)
                $path = '/' . $label;

                // For non-app pages, check if parent needs to be resolved
                $parentId = 0;
                if ($type !== 'app' && !empty($parent)) {
                    $parentLabel = trim($parent);
                    
                    // Check if parent exists in database
                    $parentPage = $parentPagesFromDb->get($parentLabel);
                    
                    if ($parentPage) {
                        // Parent found in database
                        $parentId = $parentPage->id;
                        $path = '/' . $parentPage->label . '/' . $label;
                        Log::info("PageImport: Page '{$name}' will have parent '{$parentLabel}' (ID: {$parentId}) from database");
                    } elseif (isset($batchPagesMap[$parentLabel])) {
                        // Parent is in the current batch - will resolve after insert
                        $parentItem = $batchPagesMap[$parentLabel];
                        $parentItemType = !empty($parentItem['type']) ? trim($parentItem['type']) : 'index';
                        
                        // Only use parent from batch if it's not an "app" type
                        if ($parentItemType !== 'app') {
                            $parentRelations[$label] = $parentLabel;
                            // Calculate path using parent label from batch
                            $parentItemLabel = !empty($parentItem['label']) ? trim($parentItem['label']) : Str::slug(trim($parentItem['name']));
                            $path = '/' . $parentItemLabel . '/' . $label;
                            Log::info("PageImport: Page '{$name}' will have parent '{$parentLabel}' from batch (will resolve after insert)");
                        } else {
                            Log::warning("PageImport: Parent '{$parentLabel}' is type 'app', cannot be used as parent for page '{$name}'");
                        }
                    } else {
                        Log::warning("PageImport: Parent '{$parentLabel}' not found for page '{$name}' (checked database and current batch)");
                    }
                }

                // Check if path already exists
                $existingByPath = Page::where('path', $path)->first();
                if ($existingByPath) {
                    Log::info("PageImport: Skipped page '{$name}' (path '{$path}' already exists)");
                    $skipped++;
                    continue;
                }

                // Prepare insert data
                $inserts[] = [
                    'name' => $name,
                    'label' => $label,
                    'path' => $path,
                    'parent_id' => $parentId, // Will be updated after insert if parent was in batch
                    'workflow_id' => 0,
                    'document_type_id' => 0,
                    'type' => $type,
                    'icon' => $icon,
                    'description' => null,
                    'meta_data' => null,
                    'is_menu' => false,
                    'is_disabled' => false,
                    'is_default' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Track this page to avoid duplicates in the same batch
                $existingPages->put($label, (object)['label' => $label]);
            }

            // Step 7: Insert all pages
            if (empty($inserts)) {
                Log::warning("PageImport: No valid records found for insertion.");
                return [
                    'inserted' => 0,
                    'skipped' => $skipped,
                    'total' => $total,
                ];
            }

            try {
                $this->pageRepository->insert($inserts);
                $inserted = count($inserts);
                Log::info("PageImport: Successfully inserted {$inserted} pages.");

                // Step 8: Fetch all inserted pages for further processing
                $insertedPageLabels = array_map(fn($insert) => $insert['label'], $inserts);
                $insertedPages = Page::whereIn('label', $insertedPageLabels)->get()->keyBy('label');
                Log::info("PageImport: Fetched " . $insertedPages->count() . " inserted pages for relationship setup.");

                // Step 9: Resolve parent relationships for pages that had parents in the batch
                if (!empty($parentRelations)) {
                    Log::info("PageImport: Resolving " . count($parentRelations) . " parent relationships from batch.");
                    
                    foreach ($parentRelations as $childLabel => $parentLabel) {
                        $childPage = $insertedPages->get($childLabel);
                        $parentPage = $insertedPages->get($parentLabel);
                        
                        if ($childPage && $parentPage) {
                            // Update parent_id
                            $childPage->parent_id = $parentPage->id;
                            
                            // Recalculate path with correct parent
                            $childPage->path = '/' . $parentPage->label . '/' . $childPage->label;
                            
                            $childPage->save();
                            
                            Log::info("PageImport: Set parent_id for '{$childPage->name}' to '{$parentPage->name}' (ID: {$parentPage->id})");
                        } else {
                            if (!$childPage) {
                                Log::error("PageImport: Child page '{$childLabel}' not found after insert!");
                            }
                            if (!$parentPage) {
                                Log::error("PageImport: Parent page '{$parentLabel}' not found after insert!");
                            }
                        }
                    }
                }

                // Step 10: Attach role and group to all inserted pages
                if ($superAdminRole || $enterpriseAdminGroup) {
                    Log::info("PageImport: Attaching roles and groups to " . $insertedPages->count() . " pages.");
                    
                    foreach ($insertedPages as $page) {
                        // Attach Super Administrator role
                        if ($superAdminRole) {
                            try {
                                $existingRoleIds = $page->roles->pluck('id')->toArray();
                                if (!in_array($superAdminRole->id, $existingRoleIds)) {
                                    $page->roles()->attach($superAdminRole->id);
                                    Log::debug("PageImport: Attached Super Administrator role to page '{$page->name}'");
                                }
                            } catch (\Exception $e) {
                                Log::error("PageImport: Failed to attach role to page '{$page->name}': " . $e->getMessage());
                            }
                        }

                        // Attach Enterprise Administrators group
                        if ($enterpriseAdminGroup) {
                            try {
                                if (method_exists($page, 'groups')) {
                                    $existingGroupIds = $page->groups->pluck('id')->toArray();
                                    if (!in_array($enterpriseAdminGroup->id, $existingGroupIds)) {
                                        $page->groups()->attach($enterpriseAdminGroup->id);
                                        Log::debug("PageImport: Attached Enterprise Administrators group to page '{$page->name}'");
                                    }
                                } else {
                                    // Fallback: Use direct DB insert if method doesn't exist
                                    $exists = DB::table('groupables')
                                        ->where('group_id', $enterpriseAdminGroup->id)
                                        ->where('groupable_id', $page->id)
                                        ->where('groupable_type', Page::class)
                                        ->exists();
                                    
                                    if (!$exists) {
                                        DB::table('groupables')->insert([
                                            'group_id' => $enterpriseAdminGroup->id,
                                            'groupable_id' => $page->id,
                                            'groupable_type' => Page::class,
                                        ]);
                                        Log::debug("PageImport: Attached Enterprise Administrators group to page '{$page->name}' (via direct DB)");
                                    }
                                }
                            } catch (\Exception $e) {
                                Log::error("PageImport: Failed to attach group to page '{$page->name}': " . $e->getMessage());
                            }
                        }
                    }
                    
                    Log::info("PageImport: Completed attaching roles and groups to all pages.");
                } else {
                    Log::warning("PageImport: No role or group found, skipping relationship attachment.");
                }

            } catch (\Exception $e) {
                Log::error("PageImport: Database error: " . $e->getMessage());
                Log::error("PageImport: Stack trace: " . $e->getTraceAsString());
                throw $e;
            }

            return [
                'inserted' => $inserted,
                'skipped' => $skipped,
                'total' => $total,
            ];
        });
    }
}

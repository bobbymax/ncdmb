<?php

namespace App\Imports;

use App\Interfaces\Importable;
use App\Models\Page;
use App\Repositories\PageRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PageImport implements Importable
{
    // Registry to track all saved pages across chunks (label => id)
    private static array $savedPagesRegistry = [];

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
            $deferredPages = [];

            // Step 1: Validate and prepare pages
            $validPages = [];
            foreach ($rows as $index => $item) {
                // Validate required fields
                if (empty($item['name']) || empty($item['label'])) {
                    Log::warning("PageImport: Skipping row " . ($index + 1) . " - missing name or label");
                    $skipped++;
                    continue;
                }

                // Normalize data (id is excluded - database handles auto-increment)
                $page = [
                    'parent_id' => $item['parent_id'] ?? 0,
                    'workflow_id' => $item['workflow_id'] ?? 0,
                    'document_type_id' => $item['document_type_id'] ?? 0,
                    'name' => trim($item['name']),
                    'label' => trim($item['label']),
                    'parent' => isset($item['parent']) ? trim($item['parent']) : '',
                    'path' => isset($item['path']) ? trim($item['path']) : '',
                    'icon' => isset($item['icon']) ? trim($item['icon']) : null,
                    'type' => isset($item['type']) ? trim($item['type']) : 'index',
                    'created_at' => isset($item['created_at']) ? $item['created_at'] : now(),
                    'updated_at' => isset($item['updated_at']) ? $item['updated_at'] : now(),
                ];

                // Validate type
                $validTypes = ['app', 'index', 'view', 'form', 'external', 'dashboard', 'report'];
                if (!in_array($page['type'], $validTypes)) {
                    Log::warning("PageImport: Invalid type '{$page['type']}' for page '{$page['name']}', defaulting to 'index'");
                    $page['type'] = 'index';
                }

                // Check for duplicates in database
                $existingPage = Page::where('label', $page['label'])->first();
                if ($existingPage) {
                    Log::info("PageImport: Skipped page '{$page['name']}' (label '{$page['label']}' already exists)");
                    $skipped++;
                    continue;
                }

                $validPages[] = $page;
            }

            if (empty($validPages)) {
                Log::warning("PageImport: No valid pages to process.");
                return [
                    'inserted' => 0,
                    'skipped' => $skipped,
                    'total' => $total,
                ];
            }

            // Step 2: Sort pages by dependency (parents before children)
            $sortedPages = $this->sortByDependency($validPages);

            // Step 3: Process each page in dependency order
            foreach ($sortedPages as $page) {
                $parentId = $this->resolveParent($page, $sortedPages);

                if ($parentId === null && $page['type'] !== 'app' && empty($page['parent'])) {
                    // Page needs parent but parent not found - defer
                    Log::info("PageImport: Deferring page '{$page['name']}' - parent required but not found");
                    $deferredPages[] = $page;
                    continue;
                }

                // Calculate path
                $path = $this->calculatePath($page, $parentId);

                // Check if path already exists
                $existingByPath = Page::where('path', $path)->first();
                if ($existingByPath) {
                    Log::info("PageImport: Skipped page '{$page['name']}' (path '{$path}' already exists)");
                    $skipped++;
                    continue;
                }

                // Prepare insert data
                $insertData = [
                    'name' => $page['name'],
                    'label' => $page['label'],
                    'path' => $path,
                    'parent_id' => $parentId ?? 0,
                    'workflow_id' => $page['workflow_id'],
                    'document_type_id' => $page['document_type_id'],
                    'type' => $page['type'],
                    'icon' => $page['icon'],
                    'description' => null,
                    'meta_data' => null,
                    'is_menu' => false,
                    'is_disabled' => false,
                    'is_default' => false,
                    'created_at' => $page['created_at'],
                    'updated_at' => $page['updated_at'],
                ];

                try {
                    $savedPage = Page::create($insertData);
                    $inserted++;
                    
                    // Update registry
                    self::$savedPagesRegistry[$page['label']] = $savedPage->id;
                    
                    Log::info("PageImport: Inserted page '{$page['name']}' (ID: {$savedPage->id}, Parent ID: " . ($parentId ?? 0) . ")");
                } catch (\Exception $e) {
                    Log::error("PageImport: Failed to insert page '{$page['name']}': " . $e->getMessage());
                    $skipped++;
                }
            }

            // Step 4: Process deferred pages
            if (!empty($deferredPages)) {
                Log::info("PageImport: Processing " . count($deferredPages) . " deferred pages");
                
                foreach ($deferredPages as $page) {
                    $parentId = $this->resolveParent($page, $sortedPages, true); // Check previous chunks
                    
                    if ($parentId === null) {
                        // Parent still not found - skip orphan
                        Log::warning("PageImport: Skipping orphan page '{$page['name']}' - parent '{$page['parent']}' never found");
                        $skipped++;
                        continue;
                    }

                    // Calculate path
                    $path = $this->calculatePath($page, $parentId);

                    // Check if path already exists
                    $existingByPath = Page::where('path', $path)->first();
                    if ($existingByPath) {
                        Log::info("PageImport: Skipped deferred page '{$page['name']}' (path '{$path}' already exists)");
                        $skipped++;
                        continue;
                    }

                    // Prepare insert data
                    $insertData = [
                        'name' => $page['name'],
                        'label' => $page['label'],
                        'path' => $path,
                        'parent_id' => $parentId,
                        'workflow_id' => $page['workflow_id'],
                        'document_type_id' => $page['document_type_id'],
                        'type' => $page['type'],
                        'icon' => $page['icon'],
                        'description' => null,
                        'meta_data' => null,
                        'is_menu' => false,
                        'is_disabled' => false,
                        'is_default' => false,
                        'created_at' => $page['created_at'],
                        'updated_at' => $page['updated_at'],
                    ];

                    try {
                        $savedPage = Page::create($insertData);
                        $inserted++;
                        
                        // Update registry
                        self::$savedPagesRegistry[$page['label']] = $savedPage->id;
                        
                        Log::info("PageImport: Inserted deferred page '{$page['name']}' (ID: {$savedPage->id}, Parent ID: {$parentId})");
                    } catch (\Exception $e) {
                        Log::error("PageImport: Failed to insert deferred page '{$page['name']}': " . $e->getMessage());
                        $skipped++;
                    }
                }
            }

            return [
                'inserted' => $inserted,
                'skipped' => $skipped,
                'total' => $total,
            ];
        });
    }

    /**
     * Sort pages by dependency (parents before children)
     */
    private function sortByDependency(array $pages): array
    {
        // Build a map of label => page
        $pageMap = [];
        foreach ($pages as $page) {
            $pageMap[$page['label']] = $page;
        }

        // Build dependency graph
        $dependencies = [];
        foreach ($pages as $page) {
            $label = $page['label'];
            $dependencies[$label] = [];
            
            if (!empty($page['parent'])) {
                // Check if parent is in current chunk
                if (isset($pageMap[$page['parent']])) {
                    $dependencies[$label][] = $page['parent'];
                }
            }
        }

        // Topological sort
        $sorted = [];
        $visited = [];
        $visiting = [];

        $visit = function ($label) use (&$sorted, &$visited, &$visiting, &$pageMap, &$dependencies, &$visit) {
            if (isset($visiting[$label])) {
                // Circular dependency detected - add anyway
                if (!isset($visited[$label])) {
                    $sorted[] = $pageMap[$label];
                    $visited[$label] = true;
                }
                return;
            }

            if (isset($visited[$label])) {
                return;
            }

            $visiting[$label] = true;

            // Visit dependencies first
            foreach ($dependencies[$label] as $dep) {
                if (isset($pageMap[$dep])) {
                    $visit($dep);
                }
            }

            unset($visiting[$label]);
            $sorted[] = $pageMap[$label];
            $visited[$label] = true;
        };

        foreach ($pages as $page) {
            if (!isset($visited[$page['label']])) {
                $visit($page['label']);
            }
        }

        return $sorted;
    }

    /**
     * Resolve parent for a page
     * 
     * @param array $page The page data
     * @param array $currentChunk All pages in current chunk
     * @param bool $checkRegistry Whether to check saved pages registry (for deferred pages)
     * @return int|null Parent ID or null if not found
     */
    private function resolveParent(array $page, array $currentChunk, bool $checkRegistry = false): ?int
    {
        // If parent is empty and type is 'app', no parent needed
        if (empty($page['parent']) && $page['type'] === 'app') {
            return null;
        }

        // If parent is empty and type is not 'app', parent is required
        if (empty($page['parent'])) {
            return null;
        }

        $parentLabel = $page['parent'];

        // Step 1: Check database
        $parentPage = Page::where('label', $parentLabel)->first();
        if ($parentPage) {
            // Update registry if not already there
            if (!isset(self::$savedPagesRegistry[$parentLabel])) {
                self::$savedPagesRegistry[$parentLabel] = $parentPage->id;
            }
            return $parentPage->id;
        }

        // Step 2: Check saved pages registry (for previous chunks)
        if ($checkRegistry && isset(self::$savedPagesRegistry[$parentLabel])) {
            return self::$savedPagesRegistry[$parentLabel];
        }

        // Step 3: Check current chunk
        // Since we process in dependency order, parent should already be saved
        // Check registry first (parent might have been saved already)
        if (isset(self::$savedPagesRegistry[$parentLabel])) {
            return self::$savedPagesRegistry[$parentLabel];
        }

        // If not in registry yet, check if parent exists in chunk
        // (This should be rare due to sorting, but handle it anyway)
        foreach ($currentChunk as $chunkPage) {
            if ($chunkPage['label'] === $parentLabel) {
                // Parent is in current chunk but not saved yet
                // This shouldn't happen with proper sorting, but return null to defer
                return null;
            }
        }

        // Parent not found
        return null;
    }

    /**
     * Calculate path for a page
     * 
     * @param array $page The page data
     * @param int|null $parentId The parent ID if available
     * @return string The calculated path
     */
    private function calculatePath(array $page, ?int $parentId): string
    {
        // If path is provided, use it
        if (!empty($page['path'])) {
            return $page['path'];
        }

        // If type is 'app', use just the label
        if ($page['type'] === 'app') {
            return '/' . $page['label'];
        }

        // If parent exists, build path from parent
        if ($parentId) {
            $parentPage = Page::find($parentId);
            if ($parentPage) {
                return '/' . $parentPage->label . '/' . $page['label'];
            }
        }

        // Fallback: just use label
        return '/' . $page['label'];
    }

    /**
     * Clear the saved pages registry (useful for testing or reset)
     */
    public static function clearRegistry(): void
    {
        self::$savedPagesRegistry = [];
    }

    /**
     * Get the saved pages registry (useful for debugging)
     */
    public static function getRegistry(): array
    {
        return self::$savedPagesRegistry;
    }
}

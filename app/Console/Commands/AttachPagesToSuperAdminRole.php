<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AttachPagesToSuperAdminRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pages:attach-super-admin-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attach all pages in the system to the super-administrator role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to attach pages to super-administrator role...');

        // Find the super-administrator role by slug
        $role = Role::where('slug', 'super-administrator')->first();

        if (!$role) {
            $this->error('Super Administrator role not found!');
            $this->error('Please ensure a role with slug "super-administrator" exists.');
            return 1;
        }

        $this->info("Found role: {$role->name} (ID: {$role->id})");

        // Get all pages
        $pages = Page::all();
        $totalPages = $pages->count();

        if ($totalPages === 0) {
            $this->warn('No pages found in the system.');
            return 0;
        }

        $this->info("Found {$totalPages} pages to process...");

        $attached = 0;
        $alreadyAttached = 0;
        $failed = 0;

        // Process in chunks for better performance
        $pages->chunk(100)->each(function ($pageChunk) use ($role, &$attached, &$alreadyAttached, &$failed) {
            DB::transaction(function () use ($pageChunk, $role, &$attached, &$alreadyAttached, &$failed) {
                foreach ($pageChunk as $page) {
                    try {
                        // Check if role is already attached
                        $existingRoleIds = $page->roles->pluck('id')->toArray();
                        
                        if (in_array($role->id, $existingRoleIds)) {
                            $alreadyAttached++;
                            continue;
                        }

                        // Attach the role
                        $page->roles()->attach($role->id);
                        $attached++;

                        $this->line("  ✓ Attached role to page: {$page->name} (ID: {$page->id})");
                    } catch (\Exception $e) {
                        $failed++;
                        $this->error("  ✗ Failed to attach role to page: {$page->name} (ID: {$page->id}) - {$e->getMessage()}");
                    }
                }
            });
        });

        // Summary
        $this->newLine();
        $this->info('=== Summary ===');
        $this->info("Total pages: {$totalPages}");
        $this->info("Newly attached: {$attached}");
        $this->info("Already attached: {$alreadyAttached}");
        
        if ($failed > 0) {
            $this->warn("Failed: {$failed}");
        }

        $this->newLine();
        $this->info('✓ Process completed successfully!');

        return 0;
    }
}


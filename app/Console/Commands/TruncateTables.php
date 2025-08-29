<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TruncateTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:truncate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate selected database tables';

    // Add your table names here
    protected array $tables = [
        'document_drafts',
        'document_updates',
        'documents',
        'expenses',
        'claims',
        'uploads',
        'signatures',
        'expenditures',
        'payment_batches',
        'payments',
        'signature_requests',
        'transactions',
        'document_hierarchy',
        'progress_trackers',
        'workflows',
        'journals',
        'mailing_lists',
        'projects',
        'trackables',
        'widgets',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->warn('⚠️ Truncating the following tables:');
        foreach ($this->tables as $table) {
            $this->line(" - {$table}");
        }

        if ($this->confirm('Do you really wish to truncate these tables? This cannot be undone.')) {
            Schema::disableForeignKeyConstraints();

            foreach ($this->tables as $table) {
                DB::table($table)->truncate();
                $this->info("✅ Truncated: {$table}");
            }

            Schema::enableForeignKeyConstraints();
            $this->info('🎉 All specified tables have been truncated.');
        } else {
            $this->info('🛑 Operation cancelled.');
        }
    }
}

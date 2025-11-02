<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AppCleanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean {--optimize : Run optimization after cleaning}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean all Laravel caches, routes, configs, views, and queues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->newLine();
        $this->info('🧹 Starting application cleanup...');
        $this->newLine();

        // Clear application cache
        $this->line('  → Clearing application cache...');
        $this->call('cache:clear');

        // Clear config cache
        $this->line('  → Clearing config cache...');
        $this->call('config:clear');

        // Clear route cache
        $this->line('  → Clearing route cache...');
        $this->call('route:clear');

        // Clear view cache
        $this->line('  → Clearing view cache...');
        $this->call('view:clear');

        // Clear event cache
        $this->line('  → Clearing event cache...');
        $this->call('event:clear');

        // Clear queue failed jobs
        $this->line('  → Clearing failed queue jobs...');
        $this->call('queue:clear');

        // Restart queue workers
        $this->line('  → Restarting queue workers...');
        $this->call('queue:restart');

        // Clear all optimization caches
        $this->line('  → Clearing all optimization caches...');
        $this->call('optimize:clear');

        $this->newLine();
        $this->info('✨ Application cleanup completed!');
        $this->newLine();

        // Run optimization if flag is set
        if ($this->option('optimize')) {
            $this->info('⚡ Running optimization...');
            $this->newLine();

            $this->line('  → Caching config...');
            $this->call('config:cache');

            $this->line('  → Caching routes...');
            $this->call('route:cache');

            $this->line('  → Caching views...');
            $this->call('view:cache');

            $this->line('  → Caching events...');
            $this->call('event:cache');

            $this->line('  → Optimizing application...');
            $this->call('optimize');

            $this->newLine();
            $this->info('🚀 Optimization completed!');
            $this->newLine();
        } else {
            $this->comment('💡 Tip: Run with --optimize flag to cache and optimize after cleaning');
            $this->comment('   Example: php artisan app:clean --optimize');
            $this->newLine();
        }

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateResource extends Command
{
    protected $signature = 'pack:generate 
                            {name : The name of the resource (PascalCase)}
                            {--force : Overwrite existing files}
                            {--dry-run : Show what would be generated without creating files}
                            {--skip-migration : Skip migration generation}
                            {--skip-controller : Skip controller generation}
                            {--skip-resource : Skip resource generation}
                            {--no-backup : Don\'t create backup files}';

    protected $description = 'Generate Model, Migration, Controller, Repository, Service, Resource & Service Provider file';

    private array $rollbackActions = [];
    private array $createdFiles = [];

    public function handle(): void
    {
        $name = $this->argument('name');

        // Validate input
        if (!$this->validateName($name)) {
            $this->error("Invalid resource name: {$name}");
            $this->info("Name must be in PascalCase (e.g., ProjectRisk, UserProfile)");
            return;
        }

        if ($this->isReservedName($name)) {
            $this->error("'{$name}' is a reserved name and cannot be used.");
            return;
        }

        // Show generation plan
        $this->showGenerationPlan($name);

        // Dry run mode
        if ($this->option('dry-run')) {
            $this->info("Dry run complete. No files were created.");
            return;
        }

        // Confirm unless --no-interaction
        if ($this->input->isInteractive() && !$this->confirm('Proceed with generation?', true)) {
            $this->info('Generation cancelled.');
            return;
        }

        try {
            $this->info("Starting resource generation for: {$name}");
            $this->newLine();

            // Create resources with progress tracking
            $generators = [
                'Model' => fn() => $this->createModel($name),
                'Migration' => fn() => $this->option('skip-migration') ? null : $this->createMigration($name),
                'Repository' => fn() => $this->createRepository($name),
                'Service' => fn() => $this->createService($name),
                'Provider' => fn() => $this->createProvider($name),
                'Controller' => fn() => $this->option('skip-controller') ? null : $this->createController($name),
                'Resource' => fn() => $this->option('skip-resource') ? null : $this->createResource($name),
            ];

            foreach ($generators as $resourceName => $generator) {
                if ($generator) {
                    $this->line("Creating {$resourceName}...");
                    $path = $generator();
                    if ($path) {
                        $this->createdFiles[$resourceName] = $path;
                    }
                }
            }

            $this->newLine();
            $this->showSuccessSummary($name, $this->createdFiles);

        } catch (\RuntimeException $e) {
            $this->newLine();
            $this->handleFailure($e, $this->createdFiles);
        } catch (\Exception $e) {
            $this->newLine();
            $this->error("Unexpected error: " . $e->getMessage());
            if ($this->output->isVerbose()) {
                $this->error("Stack trace: " . $e->getTraceAsString());
            }
            $this->handleFailure($e, $this->createdFiles);
        }
    }

    private function validateName(string $name): bool
    {
        return preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name);
    }

    private function isReservedName(string $name): bool
    {
        $reserved = ['Abstract', 'Interface', 'Trait', 'Class', 'Namespace', 'Use', 'Function', 'Model', 'Controller'];
        return in_array($name, $reserved);
    }

    private function parse($name): array
    {
        $class = Str::studly($name);
        $camel = Str::camel($name);
        return compact('class', 'camel');
    }

    private function showGenerationPlan(string $name): void
    {
        $formatted = $this->parse($name);
        $table = Str::snake(Str::pluralStudly($name));

        $this->info("📋 Generation Plan for: {$name}");
        $this->newLine();

        $rows = [
            ['Model', app_path("Models/{$formatted['class']}.php")],
            ['Repository', app_path("Repositories/{$formatted['class']}Repository.php")],
            ['Service', app_path("Services/{$formatted['class']}Service.php")],
            ['Provider', app_path("Providers/{$formatted['class']}ServiceProvider.php")],
        ];

        if (!$this->option('skip-migration')) {
            $rows[] = ['Migration', database_path("migrations/YYYY_MM_DD_HHMMSS_create_{$table}_table.php")];
        }

        if (!$this->option('skip-controller')) {
            $rows[] = ['Controller', app_path("Http/Controllers/{$formatted['class']}Controller.php")];
        }

        if (!$this->option('skip-resource')) {
            $rows[] = ['Resource', app_path("Http/Resources/{$formatted['class']}Resource.php")];
        }

        $this->table(['Resource Type', 'Path'], $rows);
        $this->newLine();
    }

    private function showSuccessSummary(string $name, array $createdFiles): void
    {
        $this->info("✅ Resource generation complete for: {$name}");
        $this->newLine();

        $this->info("📁 Created files:");
        foreach ($createdFiles as $type => $file) {
            $relativePath = str_replace(base_path() . '/', '', $file);
            $this->line("  ✓ {$type}: {$relativePath}");
        }

        $this->newLine();
        $this->info("📝 Next steps:");
        $this->line("  1. Run migrations: php artisan migrate");
        $this->line("  2. Add routes in routes/api.php or routes/web.php");
        $this->line("  3. Customize validation rules in {$name}Service::rules()");
        $this->line("  4. Define relationships in {$name} model");
    }

    private function handleFailure(\Exception $e, array $createdFiles): void
    {
        $this->error("❌ Resource generation failed: " . $e->getMessage());
        $this->newLine();

        if ($this->confirm('Rollback created files?', true)) {
            $this->info("🔄 Rolling back...");

            // Execute rollback actions
            foreach ($this->rollbackActions as $action) {
                try {
                    $action();
                } catch (\Exception $rollbackError) {
                    $this->warn("Rollback action failed: " . $rollbackError->getMessage());
                }
            }

            // Delete created files
            foreach ($createdFiles as $type => $file) {
                if (File::exists($file)) {
                    File::delete($file);
                    $this->line("  ✓ Deleted {$type}: {$file}");
                }
            }

            $this->newLine();
            $this->info("✅ Rollback complete.");
        }
    }

    private function addProviders($path, $value): void
    {
        if (!File::exists($path)) {
            throw new \RuntimeException("Providers file not found: {$path}");
        }

        // Create backup
        $backupPath = $path . '.backup';
        File::copy($path, $backupPath);

        try {
            $providers = require $path;

            // Check if provider already exists
            if (in_array($value, $providers)) {
                $this->warn("Provider already registered: {$value}");
                File::delete($backupPath);
                return;
            }

            // Add new provider
            $providers[] = $value;
            sort($providers); // Keep alphabetically sorted

            // Build new content
            $content = "<?php\n\nreturn [\n";
            foreach ($providers as $provider) {
                $content .= "    {$provider},\n";
            }
            $content .= "];\n";

            File::put($path, $content);
            File::delete($backupPath);

            // Register rollback action
            $this->rollbackActions[] = function () use ($path, $value) {
                $this->removeProvider($path, $value);
            };

        } catch (\Exception $e) {
            File::move($backupPath, $path);
            throw $e;
        }
    }

    private function removeProvider($path, $value): void
    {
        if (!File::exists($path)) {
            return;
        }

        $backupPath = $path . '.backup';
        File::copy($path, $backupPath);

        try {
            $providers = require $path;
            $providers = array_filter($providers, fn($p) => $p !== $value);

            $content = "<?php\n\nreturn [\n";
            foreach ($providers as $provider) {
                $content .= "    {$provider},\n";
            }
            $content .= "];\n";

            File::put($path, $content);
            File::delete($backupPath);

        } catch (\Exception $e) {
            File::move($backupPath, $path);
            throw $e;
        }
    }

    private function checkAndSaveFile($path, $stub, $formatted): void
    {
        if (File::exists($path) && !$this->option('force')) {
            throw new \RuntimeException(
                "File already exists: {$path}. Use --force to overwrite."
            );
        }

        // Validate stub exists
        $stubPath = resource_path("stubs/$stub.stub");
        if (!File::exists($stubPath)) {
            throw new \RuntimeException("Stub file not found: {$stubPath}");
        }

        // Create backup if overwriting
        if (File::exists($path) && $this->option('force') && !$this->option('no-backup')) {
            $backupPath = $path . '.backup-' . date('YmdHis');
            File::copy($path, $backupPath);
            $this->line("  Backup created: " . basename($backupPath));
        }

        $stubContent = File::get($stubPath);
        $replacements = [
            '{{ class }}' => $formatted['class'],
            '{{ camel }}' => $formatted['camel'],
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $stubContent);
        File::put($path, $content);
    }

    protected function createModel($name): string
    {
        $formatted = $this->parse($name);
        $modelPath = app_path("Models/{$formatted['class']}.php");
        $this->checkAndSaveFile($modelPath, 'model', $formatted);
        return $modelPath;
    }

    protected function createRepository($name): string
    {
        $formatted = $this->parse($name);
        $repositoryPath = app_path("Repositories/{$formatted['class']}Repository.php");
        $this->checkAndSaveFile($repositoryPath, 'repository', $formatted);
        return $repositoryPath;
    }

    protected function createService($name): string
    {
        $formatted = $this->parse($name);
        $servicePath = app_path("Services/{$formatted['class']}Service.php");
        $this->checkAndSaveFile($servicePath, 'service', $formatted);
        return $servicePath;
    }

    protected function createProvider($name): string
    {
        $formatted = $this->parse($name);
        $path = app_path("Providers/{$formatted['class']}ServiceProvider.php");
        $this->checkAndSaveFile($path, 'provider', $formatted);

        $providerClass = "App\\Providers\\{$formatted['class']}ServiceProvider::class";
        $this->addProviders(base_path('bootstrap/providers.php'), $providerClass);

        return $path;
    }

    protected function createController($name): string
    {
        $formatted = $this->parse($name);
        $controllerPath = app_path("Http/Controllers/{$formatted['class']}Controller.php");
        $this->checkAndSaveFile($controllerPath, 'controller', $formatted);
        return $controllerPath;
    }

    protected function createResource($name): string
    {
        $formatted = $this->parse($name);
        $this->call('make:resource', ['name' => "{$formatted['class']}Resource"]);
        return app_path("Http/Resources/{$formatted['class']}Resource.php");
    }

    protected function createMigration($name): string
    {
        $table = Str::snake(Str::pluralStudly(class_basename($name)));

        if ($this->migrationExists($table)) {
            $this->info("  Migration for {$table} already exists.");
            return $this->getMigrationPath($table);
        }

        $timestampBefore = time();
        $this->call('make:migration', ['name' => "create_{$table}_table"]);

        return $this->getMigrationCreatedAfter($timestampBefore, $table);
    }

    private function getMigrationCreatedAfter(int $timestamp, string $tableName): string
    {
        $files = File::files(database_path('migrations'));

        foreach ($files as $file) {
            if ($file->getMTime() >= $timestamp && Str::contains($file->getFilename(), $tableName)) {
                return $file->getRealPath();
        }
    }

        throw new \RuntimeException("Could not find created migration for: {$tableName}");
    }

    private function getMigrationPath(string $tableName): string
    {
        $files = File::files(database_path('migrations'));

        foreach ($files as $file) {
            if (Str::contains($file->getFilename(), "create_{$tableName}_table")) {
                return $file->getRealPath();
            }
        }

        throw new \RuntimeException("Migration not found for: {$tableName}");
    }

    private function migrationExists($name): bool
    {
        try {
            $this->getMigrationPath($name);
                return true;
        } catch (\RuntimeException $e) {
            return false;
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateResource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pack:generate {name} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Model, Migration, Controller, Repository, Service, & Service Provider file';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // 1. Fetch Name
        $name = $this->argument('name');
        $createdFiles = [];

        try {
            // Start creating resources and keep track of created files
            $createdFiles[] = $this->createModel($name);
            $createdFiles[] = $this->createMigration($name);
            $createdFiles[] = $this->createRepository($name);
            $createdFiles[] = $this->createService($name);
            $createdFiles[] = $this->createProvider($name);
            $createdFiles[] = $this->createController($name);

            $this->info("All resources created successfully.");

        } catch (\Exception $e) {
            $this->error("Resource generation failed: " . $e->getMessage());

            // Rollback created files if an error occurs
            foreach ($createdFiles as $file) {
                if (File::exists($file)) {
                    File::delete($file);
                    $this->warn("Rolled back: {$file}");
                }
            }
        }
    }

    private function parse($name): array
    {
        $class = Str::studly($name);
        $camel = Str::camel($name);
        return compact('class','camel');
    }

    protected function createProvider($name): string
    {
        $formatted = $this->parse($name);
        $path = app_path("Providers/{$formatted['class']}ServiceProvider.php");
        $this->checkAndSaveFile($path, 'provider', $formatted);

        return $path;
    }

    protected function createModel($name): string
    {
        $formatted = $this->parse($name);
        $modelPath = app_path("Models/{$formatted['class']}.php");
        $this->checkAndSaveFile($modelPath, 'model', $formatted);

        return $modelPath;
    }

    protected function createMigration($name): string
    {
        $table = Str::snake(Str::pluralStudly(class_basename($name)));

        if ($this->migrationExists($table)) {
           $this->info("Migration for {$table} already exists.");
           return '';
        }

        $this->call('make:migration', ['name' => "create_{$table}_table"]);

        return $this->getLatestMigrationFile();
    }

    protected function createRepository($name): string
    {
        $formatted = $this->parse($name);
        $repositoryPath = app_path("Repositories/{$formatted['class']}Repository.php");
        $this->checkAndSaveFile($repositoryPath, 'repository', $formatted);

        return $repositoryPath;
    }

    protected function createController($name): string
    {
        $formatted = $this->parse($name);
        $controllerPath = app_path("Http/Controllers/{$formatted['class']}Controller.php");
        $this->checkAndSaveFile($controllerPath, 'controller', $formatted);

        return $controllerPath;
    }

    protected function createService($name): string
    {
        $formatted = $this->parse($name);
        $servicePath = app_path("Services/{$formatted['class']}Service.php");
        $this->checkAndSaveFile($servicePath, 'service', $formatted);

        return $servicePath;
    }

    private function checkAndSaveFile($path, $stub, $formatted): void
    {
        if (File::exists($path) && !$this->option('force')) {
            $this->warn("File already exists: {$path}. Use --force to overwrite.");
        } else {
            $stub = File::get(resource_path("stubs/$stub.stub"));
            $replacements = [
                '{{ class }}' => $formatted['class'],
                '{{ camel }}' => $formatted['camel'],
            ];

            $stub = str_replace(array_keys($replacements), array_values($replacements), $stub);
            File::put($path, $stub);
        }
    }

    private function getLatestMigrationFile(): string
    {
        // Get all migration files in the 'database/migrations' directory
        $files = File::files(database_path('migrations'));

        // Sort files by the last modified time
        usort($files, function ($a, $b) {
            return $b->getMTime() - $a->getMTime(); // Sort by newest first
        });

        // Return the path of the most recently created migration file
        return $files[0]->getRealPath();
    }

    private function migrationExists($name): bool
    {
        // Get all migration files in the migrations directory
        $migrationFiles = File::files(database_path('migrations'));

        // Define the target creation migration filename pattern
        $targetPattern = "create_{$name}_table";

        // Loop through migration files and check if any file contains the table name
        foreach ($migrationFiles as $file) {
            if (Str::contains($file->getFilename(), $targetPattern)) {
                return true;
            }
        }

        return false;
    }
}

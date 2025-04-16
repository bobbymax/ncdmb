<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenerateImportService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource:import {name} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Import Service for Models, and then Generate a Job to handle resource imports';

    private const __BASE_DIR__ = 'Imports/';
    private const __MODEL_BASE_DIR__ = "App\\Models\\";
    private const __REPO_BASE_DIR__ = "App\\Repositories\\";
    private const __SUFFIX__ = "Import.php";
    private const __STUB__ = "import";

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->argument('name');
        $createdFiles = [];

        try {
            $createdFiles[] = $this->createImportService($name);

            $this->info("All resources created successfully!!");

        } catch (\Exception $e) {
            Log::error($e->getMessage());

            foreach ($createdFiles as $file) {
                if (File::exists($file)) {
                    File::delete($file);
                    $this->warn("Rolled back: {$file}");
                }
            }
        }
    }

    private function isRepositoryFilePresent(string $name): bool
    {
        $class = Str::studly($name);
        $repository = self::__REPO_BASE_DIR__.$class.'Repository';
        return class_exists($repository);
    }

    private function isModelClassPresent(string $name): bool
    {
        $class = Str::studly($name);
        $model = self::__MODEL_BASE_DIR__.$class;
        return class_exists($model);
    }

    protected function createImportService(string $name): string
    {
        if (!$this->isModelClassPresent($name) || !$this->isRepositoryFilePresent($name)) {
            Log::info("This model or repository does not exist in our structure");
            return "";
        }

        $collection = $this->parse($name);
        $importServicePath = app_path(self::__BASE_DIR__.$name.self::__SUFFIX__);

        $this->checkAndSaveFile($importServicePath, self::__STUB__, $collection);

        return $importServicePath;
    }

    private function parse(string $name): array
    {
        $model = Str::studly($name);
        $repo = $model.'Repository';
        $camel = Str::camel($name);

        return compact("model", "repo", "camel");
    }

    private function checkAndSaveFile($path, $stub, $collection): void
    {
        if (File::exists($path) && !$this->option('force')) {
            $this->warn("File already exists: {$path}. Use --force to overwrite.");
        } else {
            $stub = File::get(resource_path("stubs/$stub.stub"));
            $replacements = [
                '{{ model }}' => $collection['model'],
                '{{ repository }}' => $collection['repo'],
                '{{ camel }}' => $collection['camel'],
            ];

            $stub = str_replace(array_keys($replacements), array_values($replacements), $stub);
            File::put($path, $stub);
        }
    }
}

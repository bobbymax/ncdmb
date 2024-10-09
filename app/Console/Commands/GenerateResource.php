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
    protected $signature = 'pack:generate {name}';

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
        // 2. Create Model
        $this->createModel($name);
        // 3. Create Migration
        $this->createMigration($name);
        // 4. Create Repository
        $this->createRepository($name);
        // 5. Create Resource Service
        $this->createService($name);
        // 6. Create Service Provider
        $this->createProvider($name);
        // 7. Create Controller
        $this->createController($name);
    }

    private function parse($name): array
    {
        $class = Str::studly($name);
        $camel = Str::camel($name);
        return compact('class','camel');
    }

    protected function createProvider($name): void
    {
        $formatted = $this->parse($name);
        $path = app_path("Providers/{$formatted['class']}ServiceProvider.php");
        $this->checkAndSaveFile($path, 'provider', $formatted);
        $this->info("Provider created successfully.");
    }

    protected function createModel($name): void
    {
        $formatted = $this->parse($name);
        $modelPath = app_path("Models/{$formatted['class']}.php");
        $this->checkAndSaveFile($modelPath, 'model', $formatted);
        $this->info("Model created successfully.");
    }

    protected function createMigration($name): void
    {
        $table = Str::snake(Str::pluralStudly(class_basename($name)));
        $this->call('make:migration', ['name' => "create_{$table}_table"]);
        $this->info("Migration created successfully.");
    }

    protected function createRepository($name): void
    {
        $formatted = $this->parse($name);
        $repositoryPath = app_path("Repositories/{$formatted['class']}Repository.php");
        $this->checkAndSaveFile($repositoryPath, 'repository', $formatted);
        $this->info("Repository created successfully.");
    }

    protected function createController($name): void
    {
        $formatted = $this->parse($name);
        $controllerPath = app_path("Http/Controllers/{$formatted['class']}Controller.php");
        $this->checkAndSaveFile($controllerPath, 'controller', $formatted);
        $this->info("Controller created successfully.");
    }

    protected function createService($name): void
    {
        $formatted = $this->parse($name);
        $servicePath = app_path("Services/{$formatted['class']}Service.php");
        $this->checkAndSaveFile($servicePath, 'service', $formatted);
        $this->info("Service created successfully.");
    }

    private function checkAndSaveFile($path, $stub, $formatted): void
    {
        if (!File::exists($path)) {
            $stub = File::get(resource_path("stubs/$stub.stub"));
            $replacements = [
                '{{ class }}' => $formatted['class'],
                '{{ camel }}' => $formatted['camel'],
            ];

            $stub = str_replace(array_keys($replacements), array_values($replacements), $stub);
            File::put($path, $stub);
        }
    }
}

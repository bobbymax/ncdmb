<?php

namespace App\Core;

use App\Core\Contracts\ServiceResolverInterface;
use App\Exceptions\Processor\ServiceResolutionException;
use Illuminate\Support\Facades\{App, File, Log};
use Illuminate\Support\Str;

class ServiceResolver implements ServiceResolverInterface
{
    private array $serviceCache = [];
    private array $repositoryCache = [];
    private array $classMap = [];
    private string $serviceNamespace;
    private string $repositoryNamespace;

    public function __construct()
    {
        $this->serviceNamespace = config('processor.service_namespace', 'App\\Services\\');
        $this->repositoryNamespace = config('processor.repository_namespace', 'App\\Repositories\\');
        $this->loadServices();
        $this->loadRepositories();
    }

    public function resolve(string $key): mixed
    {
        if (isset($this->serviceCache[$key])) {
            return $this->serviceCache[$key];
        }

        try {
            // First, check if the key is already bound in the container
            if (App::bound($key)) {
                $service = App::make($key);
                $this->serviceCache[$key] = $service;
                return $service;
            }

            // Fallback: Guess from convention
            $className = $this->guessClassFromKey($key);

            if (class_exists($className)) {
                $service = App::make($className);
                $this->serviceCache[$key] = $service;
                return $service;
            }

            throw new ServiceResolutionException($key, "Service key [{$key}] could not be resolved.");

        } catch (\Throwable $e) {
            Log::error('ServiceResolver: Failed to resolve service', [
                'key' => $key,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function resolveService(string $key): mixed
    {
        return $this->resolve($key);
    }

    public function resolveRepository(string $key): mixed
    {
        if (isset($this->repositoryCache[$key])) {
            return $this->repositoryCache[$key];
        }

        try {
            $repoKey = Str::endsWith($key, 'Repo') ? $key : $key . 'Repo';
            $className = $this->guessClassFromKey($repoKey);

            if (class_exists($className)) {
                $repository = App::make($className);
                $this->repositoryCache[$key] = $repository;
                return $repository;
            }

            throw new ServiceResolutionException($key, "Repository key [{$key}] could not be resolved.");

        } catch (\Throwable $e) {
            Log::error('ServiceResolver: Failed to resolve repository', [
                'key' => $key,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function isBound(string $key): bool
    {
        return App::bound($key) || isset($this->classMap[$key]);
    }

    public function getServiceMap(): array
    {
        return $this->classMap;
    }

    protected function guessClassFromKey(string $key): string
    {
        $isRepo = Str::endsWith($key, 'Repo');
        $base = $isRepo ? Str::replaceLast('Repo', '', $key) : $key;
        $studly = Str::studly($base);

        return $isRepo
            ? "{$this->repositoryNamespace}{$studly}Repository"
            : "{$this->serviceNamespace}{$studly}Service";
    }

    protected function loadServices(): void
    {
        if (!config('processor.cache_enabled', true)) {
            return;
        }

        $path = app_path('Services');

        if (!is_dir($path)) {
            return;
        }

        foreach (File::files($path) as $file) {
            $name = $file->getFilenameWithoutExtension();
            if (Str::endsWith($name, 'Service')) {
                $shortKey = Str::camel(Str::replaceLast('Service', '', $name));
                $fqcn = $this->serviceNamespace . $name;
                $this->classMap[$shortKey] = $fqcn;
            }
        }
    }

    protected function loadRepositories(): void
    {
        if (!config('processor.cache_enabled', true)) {
            return;
        }

        $path = app_path('Repositories');

        if (!is_dir($path)) {
            return;
        }

        foreach (File::files($path) as $file) {
            $name = $file->getFilenameWithoutExtension();
            if (Str::endsWith($name, 'Repository')) {
                $shortKey = Str::camel(Str::replaceLast('Repository', '', $name)) . 'Repo';
                $fqcn = $this->repositoryNamespace . $name;
                $this->classMap[$shortKey] = $fqcn;
            }
        }
    }

    public function clearCache(): void
    {
        $this->serviceCache = [];
        $this->repositoryCache = [];
    }

    public function getCacheStats(): array
    {
        return [
            'service_cache_count' => count($this->serviceCache),
            'repository_cache_count' => count($this->repositoryCache),
            'class_map_count' => count($this->classMap),
        ];
    }
}

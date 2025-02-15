<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class ServiceBindingProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $servicePath = app_path('Services');
        $files = scandir($servicePath);

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $serviceName = pathinfo($file, PATHINFO_FILENAME);
                $className = "App\\Services\\{$serviceName}";

                if (file_exists("{$servicePath}/{$file}") && class_exists($className) && str_ends_with($serviceName, 'Service')) {
                    $key = strtolower(str_replace('Service', '', $serviceName));

                    App::bind($key, fn () => app($className));
                }
            }
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Processor Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the Processor system including service
    | resolution, caching, and namespace settings.
    |
    */

    'service_namespace' => env('PROCESSOR_SERVICE_NAMESPACE', 'App\\Services\\'),
    'repository_namespace' => env('PROCESSOR_REPOSITORY_NAMESPACE', 'App\\Repositories\\'),
    
    'cache_enabled' => env('PROCESSOR_CACHE_ENABLED', true),
    'cache_ttl' => env('PROCESSOR_CACHE_TTL', 3600),
    
    'logging_enabled' => env('PROCESSOR_LOGGING_ENABLED', true),
    'debug_mode' => env('PROCESSOR_DEBUG_MODE', false),
];

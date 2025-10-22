<?php

namespace App\Core;

use App\Core\Contracts\ResourceResolverInterface;
use App\Core\Contracts\ServiceResolverInterface;
use App\Exceptions\Processor\ResourceResolutionException;
use Illuminate\Support\Facades\Log;

class ResourceResolver implements ResourceResolverInterface
{
    private ServiceResolverInterface $serviceResolver;
    private array $resourceCache = [];
    private int $cacheHits = 0;
    private int $cacheMisses = 0;

    public function __construct(ServiceResolverInterface $serviceResolver)
    {
        $this->serviceResolver = $serviceResolver;
    }

    public function resolve(string|int|array $value, string $serviceKey, array $args = []): mixed
    {
        $cacheKey = $this->generateCacheKey($value, $serviceKey, $args);

        if (config('processor.cache_enabled', true) && isset($this->resourceCache[$cacheKey])) {
            $this->cacheHits++;
            return $this->resourceCache[$cacheKey];
        }

        try {
            $service = $this->serviceResolver->resolve($serviceKey);

            if (!$service) {
                throw new ResourceResolutionException($serviceKey, $value, "Service '{$serviceKey}' could not be resolved");
            }

            Log::debug('ResourceResolver: Service resolved successfully', [
                'service_key' => $serviceKey,
                'service_class' => get_class($service),
                'value_type' => gettype($value),
                'value' => is_array($value) ? 'array[' . count($value) . ']' : $value
            ]);

            $result = $this->performResolution($value, $service, $args);

            if (config('processor.cache_enabled', true)) {
                $this->resourceCache[$cacheKey] = $result;
                $this->cacheMisses++;
            }

            return $result;

        } catch (\Throwable $e) {
            Log::error('ResourceResolver: Failed to resolve resource', [
                'value' => is_array($value) ? 'array[' . count($value) . ']' : $value,
                'service_key' => $serviceKey,
                'args' => $args,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function resolveById(int $id, string $serviceKey): mixed
    {
        return $this->resolve($id, $serviceKey);
    }

    public function resolveByColumn(string $value, string $serviceKey, string $column = 'id'): mixed
    {
        return $this->resolve($value, $serviceKey, ['column' => $column]);
    }

    public function resolveCollection(array $values, string $serviceKey, string $column = 'id'): mixed
    {
        return $this->resolve($values, $serviceKey, ['column' => $column]);
    }

    public function clearCache(): void
    {
        $this->resourceCache = [];
        $this->cacheHits = 0;
        $this->cacheMisses = 0;
    }

    public function getCacheStats(): array
    {
        return [
            'cache_hits' => $this->cacheHits,
            'cache_misses' => $this->cacheMisses,
            'hit_rate' => $this->cacheHits + $this->cacheMisses > 0 
                ? round(($this->cacheHits / ($this->cacheHits + $this->cacheMisses)) * 100, 2) 
                : 0,
            'cache_size' => count($this->resourceCache),
        ];
    }

    private function performResolution(string|int|array $value, mixed $service, array $args): mixed
    {
        $column = $args['column'] ?? 'id';
        $category = $args['category'] ?? 'single';

        if (is_numeric($value)) {
            return $this->resolveByIdInternal($value, $service);
        }

        if (is_string($value)) {
            return $this->resolveByString($value, $service, $column, $category);
        }

        if (is_array($value)) {
            return $this->resolveByArray($value, $service, $column);
        }

        throw new ResourceResolutionException(
            get_class($service), 
            $value, 
            "Invalid value type passed to resourceResolver: " . gettype($value)
        );
    }

    private function resolveByIdInternal(int $id, mixed $service): mixed
    {
        $result = $service->show($id);
        
        Log::debug('ResourceResolver: Resolved by ID', [
            'service_class' => get_class($service),
            'id' => $id,
            'result' => $result ? 'found' : 'not_found'
        ]);
        
        return $result;
    }

    private function resolveByString(string $value, mixed $service, string $column, string $category): mixed
    {
        if ($category === 'collection') {
            $result = $service->getCollectionByColumn($column, $value);
        } else {
            $result = $service->getRecordByColumn($column, $value);
        }

        Log::debug('ResourceResolver: Resolved by string', [
            'service_class' => get_class($service),
            'column' => $column,
            'value' => $value,
            'category' => $category,
            'result' => $result ? 'found' : 'not_found'
        ]);

        return $result;
    }

    private function resolveByArray(array $values, mixed $service, string $column): mixed
    {
        if (empty($values)) {
            Log::debug('ResourceResolver: Empty array provided', [
                'service_class' => get_class($service)
            ]);
            return collect();
        }

        $isNumericArray = collect($values)->every(fn ($v) => is_numeric($v));

        if ($isNumericArray) {
            $result = $service->whereIn('id', $values);
        } else {
            if (!isset($args['column'])) {
                throw new ResourceResolutionException(
                    get_class($service),
                    $values,
                    "When passing an array of strings, you must specify a [column] in args."
                );
            }
            $result = $service->whereIn($column, $values);
        }

        Log::debug('ResourceResolver: Resolved by array', [
            'service_class' => get_class($service),
            'array_size' => count($values),
            'is_numeric' => $isNumericArray,
            'column' => $column,
            'result_count' => $result ? (is_countable($result) ? count($result) : 'not_countable') : 0
        ]);

        return $result;
    }

    private function generateCacheKey(string|int|array $value, string $serviceKey, array $args): string
    {
        $valueStr = is_array($value) ? 'array_' . count($value) . '_' . md5(serialize($value)) : (string)$value;
        $argsStr = md5(serialize($args));
        return "{$serviceKey}_{$valueStr}_{$argsStr}";
    }
}

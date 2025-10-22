<?php

namespace App\Core\Contracts;

interface ResourceResolverInterface
{
    /**
     * Resolve a resource by value, service key, and optional arguments
     */
    public function resolve(string|int|array $value, string $serviceKey, array $args = []): mixed;

    /**
     * Resolve a resource by ID
     */
    public function resolveById(int $id, string $serviceKey): mixed;

    /**
     * Resolve a resource by column value
     */
    public function resolveByColumn(string $value, string $serviceKey, string $column = 'id'): mixed;

    /**
     * Resolve a collection of resources
     */
    public function resolveCollection(array $values, string $serviceKey, string $column = 'id'): mixed;

    /**
     * Clear resource cache
     */
    public function clearCache(): void;

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array;
}

<?php

namespace App\Core\Contracts;

interface ServiceResolverInterface
{
    /**
     * Resolve a service by key
     */
    public function resolve(string $key): mixed;

    /**
     * Resolve a service specifically
     */
    public function resolveService(string $key): mixed;

    /**
     * Resolve a repository specifically
     */
    public function resolveRepository(string $key): mixed;

    /**
     * Check if a service is bound in the container
     */
    public function isBound(string $key): bool;

    /**
     * Get all available service mappings
     */
    public function getServiceMap(): array;
}

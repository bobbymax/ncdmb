<?php

namespace App\Traits;

trait ServiceAction
{
    protected function toDelete(array $backendIds, array $frontendIds): array
    {
        return array_diff($backendIds, $frontendIds);
    }

    protected function isolateKeys(array $collection, string $column): array
    {
        return array_column(json_decode(json_encode($collection), true), $column);
    }

    protected function isolateCollectionKeys(iterable $collection, string $column): array
    {
        return collect($collection)->pluck($column)->all();
    }

    protected function handleIdDeletions(array $collection, string $column, array $backendIds): array
    {
        $frontendIds = $this->isolateKeys($collection, $column);
        return $this->toDelete($backendIds, $frontendIds);
    }
}

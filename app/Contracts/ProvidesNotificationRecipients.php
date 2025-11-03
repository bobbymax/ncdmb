<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

interface ProvidesNotificationRecipients
{
    /**
     * Resolve notification recipients for a model action
     * 
     * @param Model $model The model instance
     * @param string $action The action (created, updated, deleted)
     * @return array Array of user IDs
     */
    public function resolveNotificationRecipients(Model $model, string $action): array;
    
    /**
     * Get notification metadata for the model
     * 
     * @param Model $model
     * @return array
     */
    public function getNotificationMetadata(Model $model): array;
    
    /**
     * Get resource data snapshot for notification
     * 
     * @param Model $model
     * @return array
     */
    public function getNotificationResourceData(Model $model): array;
}


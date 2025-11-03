<?php

namespace App\Traits;

use App\Observers\ResourceNotificationObserver;

trait NotifiesOnChanges
{
    public static function bootNotifiesOnChanges(): void
    {
        static::observe(ResourceNotificationObserver::class);
    }
    
    /**
     * Get the repository class for this model
     */
    public function getRepositoryClass(): string
    {
        $modelName = class_basename($this);
        return "App\\Repositories\\{$modelName}Repository";
    }
}


<?php

namespace App\Observers;

use App\Contracts\ProvidesNotificationRecipients;
use App\DTOs\ResourceNotificationContext;
use App\Services\ResourceNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ResourceNotificationObserver
{
    public function created(Model $model): void
    {
        $this->dispatchNotification($model, 'created');
    }

    public function updated(Model $model): void
    {
        $this->dispatchNotification($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->dispatchNotification($model, 'deleted');
    }

    protected function dispatchNotification(Model $model, string $action): void
    {
        dispatch(function () use ($model, $action) {
            try {
                // Get repository class
                $repositoryClass = $model->getRepositoryClass();
                
                if (!class_exists($repositoryClass)) {
                    Log::info("ResourceNotificationObserver: Repository class not found: {$repositoryClass}");
                    return;
                }
                
                $repository = app($repositoryClass);
                
                // Check if repository implements the interface
                if (!$repository instanceof ProvidesNotificationRecipients) {
                    Log::info("ResourceNotificationObserver: Repository does not provide notification recipients: {$repositoryClass}");
                    return;
                }
                
                // Resolve recipients using repository logic
                $recipients = $repository->resolveNotificationRecipients($model, $action);
                
                if (empty($recipients)) {
                    Log::info("ResourceNotificationObserver: No recipients for {$action} on " . class_basename($model), [
                        'model_id' => $model->id
                    ]);
                    return;
                }
                
                // Build context automatically
                $context = new ResourceNotificationContext(
                    repositoryClass: class_basename($repositoryClass),
                    resourceType: Str::snake(class_basename($model)),
                    resourceId: $model->id,
                    action: $action,
                    actorId: auth()->id() ?? $model->created_by_id ?? $model->user_id ?? 0,
                    recipients: $recipients,
                    resourceData: $repository->getNotificationResourceData($model),
                    metadata: $repository->getNotificationMetadata($model)
                );
                
                // Dispatch notification
                app(ResourceNotificationService::class)->notify($context);
                
                Log::info("ResourceNotificationObserver: Notification dispatched", [
                    'model' => class_basename($model),
                    'model_id' => $model->id,
                    'action' => $action,
                    'recipients' => count($recipients)
                ]);

            } catch (\Throwable $e) {
                Log::error("ResourceNotificationObserver: Failed to dispatch notification", [
                    'model' => class_basename($model),
                    'model_id' => $model->id ?? 'unknown',
                    'action' => $action,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Don't throw - notifications should not break the main process
            }
        }); // Note: No ->afterCommit() - model already saved, transaction committed
    }
}


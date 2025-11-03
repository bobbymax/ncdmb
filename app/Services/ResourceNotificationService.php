<?php

namespace App\Services;

use App\DTOs\ResourceNotificationContext;
use App\Jobs\ProcessResourceNotificationJob;
use Illuminate\Support\Facades\Log;

class ResourceNotificationService
{
    /**
     * Send notifications for a resource action
     */
    public function notify(ResourceNotificationContext $context): void
    {
        Log::info('ResourceNotificationService: Starting notification dispatch', [
            'resource_type' => $context->resourceType,
            'resource_id' => $context->resourceId,
            'action' => $context->action,
            'recipient_count' => count($context->recipients)
        ]);

        try {
            // Validate context
            if (!$context->isValid()) {
                $missingFields = $context->getMissingFields();
                Log::error('ResourceNotificationService: Invalid context', [
                    'resource_id' => $context->resourceId,
                    'missing_fields' => $missingFields
                ]);
                throw new \InvalidArgumentException('Invalid notification context: ' . implode(', ', $missingFields));
            }

            // Dispatch the job
            // Note: afterCommit() and afterResponse() are intentionally omitted
            // because the observer already dispatches after the transaction commits
            ProcessResourceNotificationJob::dispatch($context)
                ->onQueue('notifications');

            Log::info('ResourceNotificationService: Job dispatched successfully', [
                'resource_type' => $context->resourceType,
                'resource_id' => $context->resourceId,
            ]);

        } catch (\Throwable $e) {
            Log::error('ResourceNotificationService: Failed to dispatch job', [
                'resource_id' => $context->resourceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}


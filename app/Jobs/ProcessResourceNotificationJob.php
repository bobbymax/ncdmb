<?php

namespace App\Jobs;

use App\DTOs\ResourceNotificationContext;
use App\Events\ResourceNotificationProgress;
use App\Jobs\SendResourceNotificationJob;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class ProcessResourceNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public int $tries = 3;
    public array $backoff = [5, 15, 30];

    public function __construct(public ResourceNotificationContext $context) {}

    public function handle(): void
    {
        Log::info('ProcessResourceNotificationJob: Starting', [
            'resource_type' => $this->context->resourceType,
            'resource_id' => $this->context->resourceId,
            'action' => $this->context->action,
            'recipient_count' => count($this->context->recipients)
        ]);

        try {
            // Validate context
            if (!$this->context->isValid()) {
                throw new \InvalidArgumentException('Invalid context: ' . implode(', ', $this->context->getMissingFields()));
            }

            // Broadcast initial progress
            broadcast(new ResourceNotificationProgress(
                $this->context->resourceType,
                $this->context->resourceId,
                0,
                count($this->context->recipients),
                'Preparing notifications...'
            ));

            // Load all users at once
            $users = User::whereIn('id', $this->context->recipients)->get()->keyBy('id');

            Log::info('ProcessResourceNotificationJob: Users loaded', [
                'requested' => count($this->context->recipients),
                'loaded' => $users->count()
            ]);

            // Create individual notification jobs
            $jobs = collect($this->context->recipients)->map(function ($userId, $index) use ($users) {
                $user = $users->get($userId);
                
                if (!$user) {
                    Log::warning('ProcessResourceNotificationJob: User not found', ['user_id' => $userId]);
                    return null;
                }

                return new SendResourceNotificationJob(
                    $this->context,
                    $user,
                    $index + 1 // position for progress tracking
                );
            })->filter()->values();

            if ($jobs->isEmpty()) {
                Log::warning('ProcessResourceNotificationJob: No valid jobs created');
                return;
            }

            // Dispatch batch of notification jobs
            Bus::batch($jobs->toArray())
                ->name("resource-notifications:{$this->context->resourceType}:{$this->context->resourceId}")
                ->allowFailures()
                ->onQueue('notifications')
                ->dispatch();

            Log::info('ProcessResourceNotificationJob: Batch dispatched', [
                'job_count' => $jobs->count()
            ]);

        } catch (\Throwable $e) {
            Log::error('ProcessResourceNotificationJob: Failed', [
                'resource_id' => $this->context->resourceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Broadcast error
            broadcast(new ResourceNotificationProgress(
                $this->context->resourceType,
                $this->context->resourceId,
                0,
                count($this->context->recipients),
                'Failed: ' . $e->getMessage(),
                true
            ));

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessResourceNotificationJob: Job failed permanently', [
            'resource_id' => $this->context->resourceId,
            'error' => $exception->getMessage()
        ]);
    }
}


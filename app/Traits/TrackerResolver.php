<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait TrackerResolver
{
    protected function findCurrentTracker($currentPointer, $trackers): array
    {
        // Validate inputs
        if (empty($currentPointer)) {
            Log::warning('TrackerResolver: Empty current pointer provided');
            return [];
        }

        if (empty($trackers) || !is_array($trackers)) {
            Log::warning('TrackerResolver: Empty or invalid trackers array provided');
            return [];
        }

        foreach ($trackers as $tracker) {
            if (is_array($tracker) && ($tracker['identifier'] ?? '') === $currentPointer) {
                return $tracker;
            }
        }

        // Log warning if no matching tracker found
        Log::warning('TrackerResolver: No tracker found for pointer', [
            'current_pointer' => $currentPointer,
            'available_trackers' => array_column($trackers, 'identifier')
        ]);

        // Fallback to first tracker if not found
        return $trackers[0] ?? [];
    }

    /**
     * Find the previous tracker based on flow type logic
     */
    protected function findPreviousTracker(array $currentTracker, $trackers): ?array
    {
        // Validate inputs
        if (empty($currentTracker) || !is_array($currentTracker)) {
            Log::warning('TrackerResolver: Invalid current tracker provided for previous tracker lookup');
            return null;
        }

        if (empty($trackers) || !is_array($trackers)) {
            Log::warning('TrackerResolver: Empty or invalid trackers array provided for previous tracker lookup');
            return null;
        }

        $currentFlowType = $currentTracker['flow_type'] ?? '';

        // If current tracker is "from" (first in flow), there's no previous tracker
        if ($currentFlowType === 'from') {
            return null;
        }

        // If current tracker is "through", previous tracker must be "from"
        if ($currentFlowType === 'through') {
            foreach ($trackers as $tracker) {
                if (($tracker['flow_type'] ?? '') === 'from') {
                    return $tracker;
                }
            }
        }

        // If current tracker is "to", previous tracker depends on whether "through" exists
        if ($currentFlowType === 'to') {
            // First check if there's a "through" tracker
            foreach ($trackers as $tracker) {
                if (($tracker['flow_type'] ?? '') === 'through') {
                    return $tracker; // "through" is the previous tracker
                }
            }

            // If no "through" tracker, then "from" is the previous tracker
            foreach ($trackers as $tracker) {
                if (($tracker['flow_type'] ?? '') === 'from') {
                    return $tracker;
                }
            }
        }

        return null;
    }
}

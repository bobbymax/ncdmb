<?php

namespace App\Policies;

use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Log;

class QueryPolicy
{
    /**
     * Determine if the user can query resources with the given scope.
     * 
     * @param User $user
     * @param string $service
     * @param array|null $requestedScopes
     * @param BaseRepository $repository
     * @return bool
     */
    public function query(User $user, string $service, ?array $requestedScopes, BaseRepository $repository): bool
    {
        $effectiveScope = $repository->getEffectiveScope();
        
        // If no scopes are requested, use the repository's effective scope
        if (empty($requestedScopes)) {
            return true; // Repository will apply effective scope automatically
        }
        
        // Validate requested scopes against user's effective scope
        $allowedScopes = $this->getAllowedScopes($effectiveScope);
        
        foreach ($requestedScopes as $requestedScope) {
            if (!in_array($requestedScope, $allowedScopes)) {
                Log::warning('Unauthorized scope request', [
                    'user_id' => $user->id,
                    'user_rank' => $user->gradeLevel->rank ?? null,
                    'effective_scope' => $effectiveScope,
                    'requested_scope' => $requestedScope,
                    'service' => $service,
                    'allowed_scopes' => $allowedScopes,
                ]);
                
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get allowed scopes based on user's effective scope.
     * Users can only request scopes equal to or more restrictive than their effective scope.
     * 
     * @param string $effectiveScope
     * @return array
     */
    private function getAllowedScopes(string $effectiveScope): array
    {
        return match ($effectiveScope) {
            'board' => ['board', 'directorate', 'departmental', 'personal'],
            'directorate' => ['directorate', 'departmental', 'personal'],
            'departmental' => ['departmental', 'personal'],
            'personal' => ['personal'],
            default => ['personal'],
        };
    }
    
    /**
     * Determine if the user can query across departments.
     * Only board and directorate users can query across departments.
     * 
     * @param User $user
     * @param BaseRepository $repository
     * @return bool
     */
    public function queryCrossDepartment(User $user, BaseRepository $repository): bool
    {
        $effectiveScope = $repository->getEffectiveScope();
        
        return in_array($effectiveScope, ['board', 'directorate']);
    }
    
    /**
     * Determine if the user can query sensitive resources.
     * 
     * @param User $user
     * @param string $service
     * @return bool
     */
    public function querySensitive(User $user, string $service): bool
    {
        // Define sensitive services that require special permissions
        $sensitiveServices = ['document', 'payment', 'transaction', 'user'];
        
        if (!in_array($service, $sensitiveServices)) {
            return true;
        }
        
        // Only board and directorate users can query sensitive services
        $userRank = (int) ($user->gradeLevel->rank ?? 99);
        $highestGroupRank = $user->groups->min('rank') ?? 99;
        $effectiveRank = min($userRank, $highestGroupRank);
        
        return $effectiveRank <= 3; // Rank 1-3 (board and directorate)
    }
}


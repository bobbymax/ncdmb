<?php

namespace App\Http\Controllers;

use App\Policies\QueryPolicy;
use App\Services\BaseService;
use App\Traits\ApiResponse;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class QueryBuilderController extends Controller
{
    use ApiResponse;

    protected QueryPolicy $queryPolicy;

    public function __construct(QueryPolicy $queryPolicy)
    {
        $this->queryPolicy = $queryPolicy;
    }

    public function __invoke(Request $request)
    {
        // Rate limiting
        $this->ensureIsNotRateLimited($request);

        $data = $request->validate([
            'service'              => ['required', 'string'],
            'conditions'           => ['sometimes', 'array'],
            'scopes'               => ['sometimes', 'array'],
            'scopes.*'             => ['string', Rule::in(['board', 'directorate', 'departmental', 'personal'])],
            'with'                 => ['sometimes', 'array'],
            'order_by'             => ['sometimes', 'array'],
            'order_by.column'      => ['required_with:order_by', 'string'],
            'order_by.direction'   => ['required_with:order_by', Rule::in(['asc', 'desc'])],
            'paginate'             => ['sometimes', 'boolean'],
            'per_page'             => ['sometimes', 'integer', 'min:1', 'max:500'],
        ]);

        $user = Auth::user();
        
        if (!$user) {
            return $this->error(null, 'Authentication required.', 401);
        }

        $processor = processor($data['service']);
        $resolvedService = $processor->getResolvedService();

        if (!$resolvedService instanceof BaseService) {
            return $this->error(null, 'Invalid service provided.', 422);
        }

        $repository = $resolvedService->getRepository();
        $requestedScopes = Arr::get($data, 'scopes', null);

        // Authorization check using policy
        if (!$this->queryPolicy->query($user, $data['service'], $requestedScopes, $repository)) {
            return $this->error(
                null,
                'You do not have permission to query with the requested scope(s).',
                403
            );
        }

        // Check if querying sensitive resources
        if (!$this->queryPolicy->querySensitive($user, $data['service'])) {
            Log::warning('Unauthorized sensitive service query attempt', [
                'user_id' => $user->id,
                'service' => $data['service'],
                'ip' => $request->ip(),
            ]);
            
            return $this->error(
                null,
                'You do not have permission to query this resource.',
                403
            );
        }

        $conditions = Arr::get($data, 'conditions', []);

        // Audit logging for sensitive queries
        $this->logQuery($user, $data, $repository);

        // Let the repository handle scope filtering through its query method
        // The repository will apply the effective scope or requested scopes
        $result = $repository->queryWithConditions(
            $conditions,
            $requestedScopes ?? [], // Pass empty array to use effective scope
            Arr::get($data, 'with', []),
            Arr::get($data, 'order_by'),
            Arr::get($data, 'paginate', false),
            Arr::get($data, 'per_page', 50)
        );

        $resource = $this->transformQueryResult($result, $data['service']);

        return $this->success($resource);
    }

    /**
     * Ensure the request is not rate limited.
     *
     * @param Request $request
     * @return void
     * @throws ValidationException
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        $user = Auth::user();
        $key = $user 
            ? 'query:' . $user->id . ':' . $request->ip()
            : 'query:' . $request->ip();

        $maxAttempts = 60; // 60 queries per minute
        $decayMinutes = 1;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            Log::warning('Query rate limit exceeded', [
                'user_id' => $user->id ?? null,
                'ip' => $request->ip(),
                'service' => $request->input('service'),
            ]);

            throw ValidationException::withMessages([
                'query' => [
                    trans('auth.throttle', [
                        'seconds' => $seconds,
                        'minutes' => ceil($seconds / 60),
                    ]),
                ],
            ]);
        }

        RateLimiter::hit($key, $decayMinutes * 60);
    }

    /**
     * Log query for audit purposes.
     *
     * @param \App\Models\User $user
     * @param array $data
     * @param \App\Repositories\BaseRepository $repository
     * @return void
     */
    protected function logQuery($user, array $data, $repository): void
    {
        $sensitiveServices = ['document', 'payment', 'transaction', 'user'];
        $isSensitive = in_array($data['service'], $sensitiveServices);
        
        $effectiveScope = $repository->getEffectiveScope();
        $requestedScopes = Arr::get($data, 'scopes', []);

        // Log all queries, but with different levels based on sensitivity
        $logData = [
            'user_id' => $user->id,
            'user_rank' => $user->gradeLevel->rank ?? null,
            'effective_scope' => $effectiveScope,
            'requested_scopes' => $requestedScopes,
            'service' => $data['service'],
            'conditions_count' => count(Arr::get($data, 'conditions', [])),
            'has_pagination' => Arr::get($data, 'paginate', false),
            'ip' => request()->ip(),
        ];

        if ($isSensitive) {
            Log::info('Sensitive query executed', $logData);
        } else {
            Log::debug('Query executed', $logData);
        }
    }

    private function transformQueryResult(mixed $result, string $serviceKey): mixed
    {
        $resourceClass = $this->resolveResourceClass($serviceKey);

        if (!$resourceClass) {
            return $result;
        }

        if ($result instanceof LengthAwarePaginator || $result instanceof Paginator) {
            return $resourceClass::collection($result);
        }

        if ($result instanceof Collection || is_array($result)) {
            return $resourceClass::collection($result);
        }

        if (is_null($result)) {
            return $result;
        }

        return new $resourceClass($result);
    }

    private function resolveResourceClass(string $serviceKey): ?string
    {
        $class = 'App\\Http\\Resources\\' . Str::studly($serviceKey) . 'Resource';

        return class_exists($class) ? $class : null;
    }
}



<?php

namespace App\Http\Controllers;

use App\Services\BaseService;
use App\Traits\ApiResponse;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class QueryBuilderController extends Controller
{
    use ApiResponse;

    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'service'              => ['required', 'string'],
            'conditions'           => ['sometimes', 'array'],
            'scopes'               => ['sometimes', 'array'],
            'with'                 => ['sometimes', 'array'],
            'order_by'             => ['sometimes', 'array'],
            'order_by.column'      => ['required_with:order_by', 'string'],
            'order_by.direction'   => ['required_with:order_by', Rule::in(['asc', 'desc'])],
            'paginate'             => ['sometimes', 'boolean'],
            'per_page'             => ['sometimes', 'integer', 'min:1'],
        ]);

        $processor = processor($data['service']);
        $resolvedService = $processor->getResolvedService();

        if (!$resolvedService instanceof BaseService) {
            return $this->error(null, 'Invalid service provided.', 422);
        }

        $conditions = Arr::get($data, 'conditions', []);

        if ($user = Auth::user()) {
            if (Arr::isAssoc($conditions)) {
                $conditions['department_id'] = $user->department_id;
            } else {
                $conditions[] = [
                    'column'   => 'department_id',
                    'operator' => '=',
                    'value'    => $user->department_id,
                ];
            }
        }

        $result = $resolvedService->query(
            $conditions,
            Arr::get($data, 'scopes', []),
            Arr::get($data, 'with', []),
            Arr::get($data, 'order_by'),
            Arr::get($data, 'paginate', false),
            Arr::get($data, 'per_page', 50)
        );

        $resource = $this->transformQueryResult($result, $data['service']);

        return $this->success($resource);
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



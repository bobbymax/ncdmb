<?php

namespace App\Traits;

trait ApiResponse
{
    protected function stored($txt): string
    {
        return $txt . " has been successfully added!!";
    }

    protected function updated($txt): string
    {
        return $txt . " has been successfully updated!!";
    }

    protected function destroyed($txt): string
    {
        return $txt . " has been deleted successfully!!";
    }

    protected function success($data, $message = null, $code = 200): \Illuminate\Http\JsonResponse
    {
        // Check if data is a paginated resource collection
        if ($data instanceof \Illuminate\Http\Resources\Json\AnonymousResourceCollection) {
            // Get the underlying paginator
            $paginator = $data->resource;
            
            if ($paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
                // Return with pagination metadata at root level
                return response()->json([
                    'status' => 'success',
                    'message' => $message,
                    'data' => $data->items(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                    'next_page_url' => $paginator->nextPageUrl(),
                    'prev_page_url' => $paginator->previousPageUrl(),
                    'first_page_url' => $paginator->url(1),
                    'last_page_url' => $paginator->url($paginator->lastPage()),
                    'path' => $paginator->path(),
                    'links' => $paginator->linkCollection()->toArray(),
                ], $code);
            }
        }
        
        // Non-paginated response (backward compatible)
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function authWithCookie($user, $token): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'user' => $user,
            'message' => 'Logged in successfully!!'
        ])->cookie('auth_token', $token, 60 * 24, '/', '.portal.test', true, true, false, 'None');
    }

    protected function error($data, $message, $code): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function noContent(): \Illuminate\Http\JsonResponse
    {
        return response()->json([]);
    }
}

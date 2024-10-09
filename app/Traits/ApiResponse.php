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
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function error($data, $message, $code): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data
        ], $code);
    }
}

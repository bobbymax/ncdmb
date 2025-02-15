<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

class HandleFormDataPutRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('PUT') || $request->isMethod('PATCH')) {
            Log::info('Handling PUT/PATCH request with FormData.');

            // Preserve existing session data before modifying request
            $sessionData = $request->session()->all();

            // Properly parse FormData
            $input = $request->all();
            foreach ($input as $key => $value) {
                $request->request->set($key, $value);
            }

            // Restore session data after modifying request
            $request->session()->replace($sessionData);
        }

        return $next($request);
    }
}

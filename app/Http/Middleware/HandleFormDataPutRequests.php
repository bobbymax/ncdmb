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
//        Log::info('Middleware Request Data: ', [$request->getContent()]);
        // Check if the request method is PUT or PATCH and the content type is multipart
        if (in_array($request->method(), ['PUT', 'PATCH']) && $request->isMethod('POST') && $request->has('_method')) {
            Log::info('Method 1: ', [$request->method()]);
            $request->setMethod($request->input('_method'));
        }

        // If the request is PUT/PATCH, parse FormData into the request
        if ($request->isMethod('PUT') || $request->isMethod('PATCH')) {
//            Log::info('Method 2: ', [$request->isMethod('PATCH')]);
            $input = $request->all(); // Get POST data
            $request->json()->add($input); // Merge into the request's JSON data
            $request->request = new InputBag($input); // Replace the request data
        }

        return $next($request);
    }
}

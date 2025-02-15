<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Pipeline;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Closure;

class CustomEnsureFrontendRequestsAreStateful
{
    /**
     * Handle the incoming requests.
     */
    public function handle($request, Closure $next)
    {
        $this->configureSecureCookieSessions();

        return (new Pipeline(app()))
            ->send($request)
            ->through(
                static::fromFrontend($request) ? $this->frontendMiddleware() : []
            )
            ->then(function ($request) use ($next) {
                return $next($request);
            });
    }

    /**
     * Configure secure cookie sessions.
     */
    protected function configureSecureCookieSessions(): void
    {
        config([
            'session.http_only' => false,
            'session.same_site' => 'none',
            'session.secure' => true,
        ]);
    }

    /**
     * Get the middleware that should be applied to requests from the "frontend".
     */
    protected function frontendMiddleware()
    {
        $middleware = array_values(array_filter(array_unique([
            config('sanctum.middleware.encrypt_cookies', \Illuminate\Cookie\Middleware\EncryptCookies::class),
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            CustomStartSession::class,
            config('sanctum.middleware.authenticate_session'),
        ])));

        // Append the Closure separately to ensure it's not inside an array
        return array_merge($middleware, [new class {
            public function handle($request, Closure $next)
            {
                $request->attributes->set('sanctum', true);
                return $next($request);
            }
        }]);
    }

    /**
     * Determine if the given request is from the first-party application frontend.
     */
    public static function fromFrontend($request)
    {
        $domain = $request->headers->get('referer') ?: $request->headers->get('origin');

        if (!$domain) {
            return false;
        }

        $domain = Str::of($domain)->replaceMatches('/^https?:\/\//', '')->finish('/');

        return Str::is(
            Collection::make(config('sanctum.stateful', []))
                ->map(fn ($uri) => trim($uri) . '/*')
                ->all(),
            $domain
        );
    }
}

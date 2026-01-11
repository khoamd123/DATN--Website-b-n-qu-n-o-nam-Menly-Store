<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Get CORS config
        $corsConfig = config('cors', []);

        // Set CORS headers
        $allowedOrigins = $corsConfig['allowed_origins'] ?? ['*'];
        $allowedMethods = $corsConfig['allowed_methods'] ?? ['*'];
        $allowedHeaders = $corsConfig['allowed_headers'] ?? ['*'];
        $maxAge = $corsConfig['max_age'] ?? 0;
        $supportsCredentials = $corsConfig['supports_credentials'] ?? false;

        // Handle preflight requests
        if ($request->getMethod() === 'OPTIONS') {
            $response = response('', 200);
        }

        // Set headers
        if (in_array('*', $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        } else {
            $origin = $request->headers->get('Origin');
            if (in_array($origin, $allowedOrigins)) {
                $response->headers->set('Access-Control-Allow-Origin', $origin);
            }
        }

        if (in_array('*', $allowedMethods)) {
            $response->headers->set('Access-Control-Allow-Methods', implode(', ', ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']));
        } else {
            $response->headers->set('Access-Control-Allow-Methods', implode(', ', $allowedMethods));
        }

        if (in_array('*', $allowedHeaders)) {
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        } else {
            $response->headers->set('Access-Control-Allow-Headers', implode(', ', $allowedHeaders));
        }

        if ($maxAge > 0) {
            $response->headers->set('Access-Control-Max-Age', $maxAge);
        }

        if ($supportsCredentials) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}









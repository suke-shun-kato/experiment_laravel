<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResponseHeaderMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response) $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // ヘッダーを追加する
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-Frame-Options', 'deny');

        return $response;
    }
}

<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 前処理
        if (!self::verifyOrigin($request->header('Origin'), config('cors.allowed_origins', []))) {
            return \response()->json(
                [
                    'message' => "This Origin couldn't handle because access control allow origin."
                ],
                Controller::STATUS_CODE_FORBIDDEN);
        }


        return $next($request);
    }

    /**
     * @param string|null $origin
     * @param string[] $allowdOrigins
     * @return bool
     */
    private static function verifyOrigin(?string $origin, array $allowdOrigins): bool {
        if (in_array('*', $allowdOrigins)) {
            return true;
        }

        if (in_array($origin, $allowdOrigins)) {
            return true;
        }

        return false;
    }
}

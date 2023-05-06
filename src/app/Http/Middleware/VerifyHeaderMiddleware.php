<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyHeaderMiddleware
{
    private const X_REQUESTED_WITH_VALUE = 'XMLHttpRequest';

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response) $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // コントローラーの前の処理
        if (!self::verifyOrigin($request->header('Origin'), config('cors.allowed_origins', []))) {
            return \response()->json(
                [
                    'message' => "This Origin couldn't handle because access control allow origin."
                ],
                Controller::STATUS_CODE_FORBIDDEN);
        }

        if (!self::verifyXRequestedWith($request->header('X-Requested-With'))) {
            return \response()->json(
                [
                    'message' => "Header doesn't contain X-Requested-With: XMLHttpRequest"
                ],
                Controller::STATUS_CODE_FORBIDDEN
            );
        }


        return $next($request);
    }

    /**
     * @param string|null $origin
     * @param string[] $allowedOrigins
     * @return bool
     */
    private static function verifyOrigin(?string $origin, array $allowedOrigins): bool {
        if (in_array('*', $allowedOrigins)) {
            return true;
        }

        if (in_array($origin, $allowedOrigins)) {
            return true;
        }

        return false;
    }

    /**
     * X-Requested-With ヘッダーが XMLHttpRequest の値であるか？
     * @param string|null $xRequestedWith X-Requested-With の値。X-Requested-With ヘッダーがない場合はnull。
     */
    private static function verifyXRequestedWith(?string $xRequestedWith): bool {
        return $xRequestedWith === self::X_REQUESTED_WITH_VALUE;
    }
}

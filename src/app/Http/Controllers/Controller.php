<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * プロジェクト初期化時にControllersディレクトリに作成されるクラス
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public const STATUS_CODE_OK = 200;
    public const STATUS_CODE_CREATED = 201;
    public const STATUS_CODE_NOT_CONTENT = 204;
    public const STATUS_CODE_BAD_REQUEST = 400;
    public const STATUS_CODE_UNAUTHORIZED = 401;
    public const STATUS_CODE_NOT_FOUND = 404;
}

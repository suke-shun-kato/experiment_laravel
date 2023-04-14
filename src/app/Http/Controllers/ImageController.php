<?php

namespace App\Http\Controllers;

use App\Models\UImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ImageController extends Controller
{
    /**
     * 画像を保存
     */
    public function store(Request $request): JsonResponse
    {
        // アップロードしたファイルを、storage/app/public/img/xxxxxx に保存
        $path = $request->file('image')->store('public/img');

        // DBに保存
        $uImage = DB::transaction(function () use ($path) {
            // UImage を作成
            return UImage::create($path, Auth::id());
        });

        return response()->json(
            $uImage->toArray(),
            self::STATUS_CODE_CREATED);
    }

}

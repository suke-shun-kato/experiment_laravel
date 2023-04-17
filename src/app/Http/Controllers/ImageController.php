<?php

namespace App\Http\Controllers;

use App\Models\UImage;
use Aws\S3\S3Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ImageController extends Controller
{
    private const IMAGE_STORAGE_LARAVEL_PATH = '/public/img';

    private static function getStorageAbsolutePath(string $laravelPath): string {
        // __DIR__はこのファイルのルートからのパス
        return realpath(__DIR__ . '/../../../storage/app/' . $laravelPath);
    }

    /**
     * 画像を保存
     */
    public function store(Request $request): JsonResponse
    {
        // アップロードした image のファイルを保存
        $path = $request->file('image')
            ->store(self::IMAGE_STORAGE_LARAVEL_PATH);  // storage/app/public/img/xxxxxx に保存

        $s3Url = null;
        try {
            // S3にアップロード
            $s3Url = self::uploadToS3($path);
        } catch (RuntimeException $e) {
            return response()->json(
                ['message' => "couldn't upload to S3. errorClass:" . get_class($e) . ", errorMessage: {$e->getMessage()}"],
                self::STATUS_CODE_BAD_REQUEST);
        } finally {
            // サーバー上のファイルを削除
            Storage::delete($path);
        }

        // DBに保存
        $uImage = DB::transaction(function () use ($s3Url) {
            // UImage を作成
            return UImage::create($s3Url, Auth::id());
        });

        return response()->json(
            $uImage->toArray(),
            self::STATUS_CODE_CREATED);
    }

    /**
     * $laravelPath にあるファイルをS3にアップロードする
     * @param string $laravelPath
     * @return string S3に保存した画像のURL
     */
    private static function uploadToS3(string $laravelPath): string {
        // IAMロールでアクセス制限をかけていいるので、profile や credentialsはいらない
        $s3Client = new S3Client([
            'version' => '2006-03-01',  // S3Client 自体のバージョン
            'region' => config('filesystems.disks.s3.region'),
        ]);
        $result = $s3Client->putObject([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $laravelPath,
            'SourceFile' => self::getStorageAbsolutePath($laravelPath)
        ]);

        if ($result["@metadata"]["statusCode"] != self::STATUS_CODE_OK) {
            throw new RuntimeException("s3に正しく保存できませんでした");
        }

        return $result["ObjectURL"];
    }

}

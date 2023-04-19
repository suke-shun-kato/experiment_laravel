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
    private const IMAGE_STORAGE_LARAVEL_PATH = '/img';

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

    private static function getS3Client(): S3Client {
        if (config('filesystems.disks.s3.use_path_style_endpoint')) {
        // ローカル環境の場合はdockerでminioを使っているのでPathスタイルで指定
            return new S3Client([
                'version' => '2006-03-01',  // S3Client 自体のバージョン
                'region' => config('filesystems.disks.s3.region'),
                'credentials' => [
                    'key' => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
                'endpoint' => config('filesystems.disks.s3.endpoint'),
                'use_path_style_endpoint' => config('filesystems.disks.s3.use_path_style_endpoint'),
            ]);
        } else {
        // 本番環境の場合
            // IAMロールでアクセス制限をかけていいるので、profile や credentialsはいらない
            return new S3Client([
                'version' => '2006-03-01',  // S3Client 自体のバージョン
                'region' => config('filesystems.disks.s3.region'),
            ]);
        }
    }

    /**
     * $laravelPath にあるファイルをS3にアップロードする
     * @param string $laravelPath
     * @return string S3に保存した画像のURL
     */
    private static function uploadToS3(string $laravelPath): string
    {
        $s3Client = self::getS3Client();

        // ファイルをS3にアップロード
        $result = $s3Client->putObject([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $laravelPath,
            'SourceFile' => self::getStorageAbsolutePath($laravelPath)
        ]);

        if ($result["@metadata"]["statusCode"] != self::STATUS_CODE_OK) {
            throw new RuntimeException("s3に正しく保存できませんでした");
        }

        return self::replaceUrl(
            $result["ObjectURL"],
            config('filesystems.disks.s3.endpoint'),
            config('filesystems.disks.s3.endpoint_host'));
    }

    private static function replaceUrl(string $objectUrl, string $endpoint, ?string $hostEndpoint): string {
        return is_null($hostEndpoint) ? $objectUrl : str_replace($endpoint, $hostEndpoint, $objectUrl);
    }

}

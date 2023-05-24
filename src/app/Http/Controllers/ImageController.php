<?php

namespace App\Http\Controllers;

use App\Models\UImage;
use Aws\S3\S3Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class ImageController extends Controller
{
    private const IMAGE_DIR_PATH_IN_APP_STORAGE = 'img/';

    private const APP_STORAGE_DIR_PATH_IN_STORAGE = 'app/';

    public const IMAGE_DIR_PATH_IN_STORAGE = self::APP_STORAGE_DIR_PATH_IN_STORAGE . self::IMAGE_DIR_PATH_IN_APP_STORAGE;

    private static function getAbsolutePath(string $fileName): string {
        return realpath(storage_path(self::IMAGE_DIR_PATH_IN_STORAGE . $fileName));
    }

    public static function getS3KeyName(string $fileName): string {
        return self::IMAGE_DIR_PATH_IN_APP_STORAGE . time() . $fileName;
    }

    public static function getRelativePathInStorage(string $fileName): string {
        return self::IMAGE_DIR_PATH_IN_APP_STORAGE . $fileName;
    }

    /**
     * 画像を保存
     */
    public function store(Request $request): JsonResponse
    {
        // バリデータを作成
        $validator = Validator::make($request->file(), [
            'image' => ['required', 'image']
        ]);

        // 必須パラメータがない場合はエラー専用のメッセージを返す
        if ($validator->fails()) {
            return response()->json([
                'message' => "The image couldn't create",
                'required' => 'image'
            ], self::STATUS_CODE_BAD_REQUEST);

        }

        // アップロードした image のファイルを storage/app/img/ に保存
        $pathInAppStorage = $request->file('image')
            ->store(self::IMAGE_DIR_PATH_IN_APP_STORAGE);
        $imgFileName = basename($pathInAppStorage);

        $s3Url = null;
        try {
            // S3にアップロード
            $s3Url = self::uploadToS3(self::getAbsolutePath($imgFileName), self::getS3KeyName($imgFileName));
        } catch (RuntimeException $e) {
            return response()->json(
                ['message' => "couldn't upload to S3. errorClass:" . get_class($e) . ", errorMessage: {$e->getMessage()}"],
                self::STATUS_CODE_BAD_REQUEST);
        } finally {
            // サーバー上のファイルを削除
            Storage::delete($pathInAppStorage);
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
     * サーバー内にあるファイルをS3にアップロードする
     * @param string $absolutePath アップロードするファイルの場所
     * @param string|null $keyPath S3のkey名。null の場合は $absolutePath がkeyになる
     * @return string S3に保存した画像のURL
     */
    public static function uploadToS3(string $absolutePath, ?string $keyPath = null): string
    {
        $s3Client = self::getS3Client();

        // ファイルをS3にアップロード
        $result = $s3Client->putObject([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $keyPath ?? $absolutePath,
            'SourceFile' => $absolutePath,
        ]);

        if ($result["@metadata"]["statusCode"] != self::STATUS_CODE_OK) {
            throw new RuntimeException("s3に正しく保存できませんでした");
        }

        return self::replaceUrl(
            $result["ObjectURL"],
            config('filesystems.disks.s3.endpoint'),
            config('filesystems.disks.s3.endpoint_host'));
    }

    private static function replaceUrl(string $objectUrl, ?string $endpoint, ?string $hostEndpoint): string {
        return is_null($hostEndpoint) || is_null($endpoint) ? $objectUrl : str_replace($endpoint, $hostEndpoint, $objectUrl);
    }

}

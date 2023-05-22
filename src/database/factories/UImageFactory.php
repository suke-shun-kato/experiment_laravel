<?php

namespace Database\Factories;

use App\Http\Controllers\ImageController;
use App\Models\UImage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends Factory<UImage>
 */
class UImageFactory extends Factory
{
    private const IMG_PATH_IN_DATABASE = 'seeders/img';

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $uploadToPath = storage_path(ImageController::IMAGE_DIR_PATH_IN_STORAGE);

        //
        self::mkdirIfNotExist($uploadToPath);

        // ファイルをサーバー内にアップ
        $path = fake()->file(
            database_path(self::IMG_PATH_IN_DATABASE),
            $uploadToPath);

        // minio（S3）にアップロード
        $s3Path = ImageController::uploadToS3($path, ImageController::getS3KeyName(basename($path)));

        // サーバー内のファイルは削除
        Storage::delete(ImageController::getRelativePathInStorage(basename($path)));

        return [
            'user_id' => User::factory(),
            'path' => $s3Path
        ];
    }

    private static function mkdirIfNotExist(string $dirPath): void {
        if (!file_exists($dirPath)) {
            mkdir($dirPath, 0700, true);
        }
    }
}

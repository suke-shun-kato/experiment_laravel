<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OutOfBoundsException;

/**
 * @property int $recipe_id
 * @property int $image_id
 */
class URecipeImage extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * モデルに関連付けるテーブル
     * @var string
     */
    protected $table = 'u_recipe_images';

    /**
     * @var string[]
     */
    protected $guarded = ['id', 'recipe_id', 'deleted_at', 'created_at', 'updated_at'];


    public function image(): BelongsTo {
        return $this->belongsTo(UImage::class, 'id', 'image_id');
    }


    public static function create(array $imageIds, int $recipeId, int $userId): Collection
    {
        //// 例外処理
        // $imageIdsの中で実際に存在している画像のimage_idを取得
        $existingImageIds = UImage::whereIn('id', $imageIds)
            ->where('user_id', $userId)
            ->get()
            ->map(function (UImage $image, int $key) {
                return $image->id;
            });

        // 存在していないimage_idがあれば例外をthrow
        $imageIdDiff = collect($imageIds)->diff($existingImageIds);
        if ($imageIdDiff->count() > 0) {
            $imageIdsStr = implode(',', $imageIdDiff->all());
            throw new OutOfBoundsException("image_id: $imageIdsStr は存在しません。" );
        }

        //// 保存
        $recipeImages = new Collection();
        foreach ($imageIds as $imageId) {
            $recipeImage = new URecipeImage();
            $recipeImage->recipe_id = $recipeId;
            $recipeImage->image_id = $imageId;
            $recipeImage->save();

            $recipeImages->add($recipeImage);
        }

        return $recipeImages;
    }
}

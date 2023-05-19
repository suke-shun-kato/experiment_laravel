<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OutOfBoundsException;

/**
 * @property int $recipe_id
 * @property int $image_id
 * @property int $id
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
    protected $guarded = ['id', 'u_recipe_id', 'deleted_at', 'created_at', 'updated_at'];


    public function uImage(): BelongsTo {
        return $this->belongsTo(UImage::class, 'u_image_id', 'id');
    }


    /**
     * u_recipes に所属する u_recipe_images を新規に作成する
     * @param \Illuminate\Support\Collection $imageIds intのCollection
     * @param URecipe $recipe
     * @param int $userId
     * @return URecipe
     */
    public static function create(\Illuminate\Support\Collection $imageIds, URecipe $recipe, int $userId): URecipe
    {
        //// 例外処理
        // $imageIdsの中で実際に存在している画像のimage_idを取得
        $existingImageIds = UImage::whereIn('id', $imageIds->toArray())
            ->where('user_id', $userId)
            ->get()
            ->map(function (UImage $image, int $key) {
                return $image->id;
            });

        // 存在していないimage_idがあれば例外をthrow
        $imageIdDiff = $imageIds->diff($existingImageIds);
        if ($imageIdDiff->count() > 0) {
            $imageIdsStr = implode(',', $imageIdDiff->all());
            throw new OutOfBoundsException("image_id: $imageIdsStr は存在しません。" );
        }

        //// 保存
        $imageIds->each(function(int $imageId, int $key) use ($recipe) {
            $recipe->uImages()->create([
                'image_id' => $imageId,
            ]);
        })->toArray();

        return URecipe::findByIdAndUserId($recipe->id, $userId);
    }


    /**
     * u_recipes に所属する u_recipe_images をDELETEしてINSERTする
     * @param URecipe $recipe
     * @param int[] $updateImageIdsAry INSERTするrecipeId
     * @param int $userId
     * @return URecipe
     */
    public static function deleteInsert(URecipe $recipe, array $updateImageIdsAry, int $userId): URecipe {
        $recipe->uImages()->delete();
        return self::create(collect($updateImageIdsAry), $recipe, $userId);
    }
}

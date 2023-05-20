<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static where(string $name, mixed $value)
 * @method static with(string $name)
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $description
 * @property Collection $images
 */
class URecipe extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * モデルに関連付けるテーブル
     * @var string
     */
    protected $table = 'u_recipes';

    /**
     * $this->fill([....]) で値をセットするときにセットできないようにするカラム名
     * @var string[]
     */
    protected $guarded = ['id', 'user_id', 'deleted_at', 'created_at', 'updated_at'];

    // BelongsToMany の方を使うので一旦コメントアウト
//    public function uRecipeImages(): HasMany
//    {
//        return $this->hasMany(URecipeImage::class, 'u_recipe_id');
//    }

    /**
     * 多対多のテーブル定義。u_recipes は u_recipe_image テーブルを通して u_images テーブルと多対多の関係である
     * @return BelongsToMany
     */
    public function images(): BelongsToMany {
        return $this->belongsToMany(Uimage::class, 'u_recipe_images', 'u_recipe_id', 'u_image_id');
    }

    /**
     * 指定のuser_idのRecipeリストを取得する
     */
    public static function getList(int $userId): Collection {
        $images =  self::with('images')->where('user_id', $userId)
            ->get();
        return $images;
    }

    /**
     * idとuserIdを指定してRecipeを取得する
     */
    public static function findByIdAndUserId(int $id, int $userId): ?URecipe {
        return self::with('images')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * $setParams
     * @param array $setParams
     * @param int|null $userId セットするユーザーID。セットしない場合はnull。
     * @return void
     */
    public function fillParams(array $setParams, ?int $userId): void {
        if (!is_null($userId)) {
            $this->user_id = $userId;
        }

        // $setParams の値を全てRecipeにセットする
        $this->fill($setParams);
    }
}

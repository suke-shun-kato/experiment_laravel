<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static where(string $name, mixed $value)
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

    public function images(): HasMany
    {
        return $this->hasMany(URecipeImage::class);
    }

    /**
     * 指定のuser_idのRecipeリストを取得する
     */
    public static function getList(int $userId): Collection {
        return self::where('user_id', $userId)->get();
    }

    /**
     * idとuserIdを指定してRecipeを取得する
     */
    public static function findByIdAndUserId(int $id, int $userId): ?URecipe {
        return self::where('id', $id)
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

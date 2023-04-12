<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

/**
 * @method static where(string $name, mixed $value)
 * @property int $user_id
 * @property string $title
 * @property string $description
 */
class URecipe extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasFactory;

    /**
     * モデルに関連付けるテーブル
     * @var string
     */
    protected $table = 'u_recipes';

    // TODO guardを設定する

    /**
     * 指定のuser_idのレシピリストを取得する
     */
    public static function getList(int $userId): Collection {
        return self::where('user_id', $userId)->get();
    }

    /**
     *
     * @param Request $request
     * @param bool $setsUserId ユーザーIOをセットするか
     * @return void
     */
    public function setRequestParamIfExists(Request $request, bool $setsUserId): void {
        if ($setsUserId) {
            $this->user_id = $request->user()->id;
        }

        if (!is_null($request->title)) {
            $this->title = $request->title;
        }
        if (!is_null($request->description)) {
            $this->description = $request->description;
        }
    }
}

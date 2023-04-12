<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

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

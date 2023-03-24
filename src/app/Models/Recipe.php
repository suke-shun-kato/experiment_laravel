<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class Recipe extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasFactory;

    /**
     * モデルに関連付けるテーブル
     * @var string
     */
    protected $table = 'recipes';

    public function setRequestParamIfExists(Request $request): void {
        if (!is_null($request->title)) {
            $this->title = $request->title;
        }
        if (!is_null($request->description)) {
            $this->description = $request->description;
        }
    }
}

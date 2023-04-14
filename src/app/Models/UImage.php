<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static Builder whereIn(string $string, array $targetImageIds)
 * @property string $path
 * @property int $user_id
 * @property int $id
 */
class UImage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'u_images';

    protected $guarded = ['id', 'user_id', 'deleted_at', 'created_at', 'updated_at'];

    public static function create(string $path, int $userId): UImage {
        $image = new UImage();
        $image->user_id = $userId;
        $image->path = $path;  // public/img/xxxxxx
        $image->save();

        return $image;
    }

}

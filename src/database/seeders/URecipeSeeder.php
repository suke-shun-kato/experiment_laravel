<?php

namespace Database\Seeders;

use App\Models\UImage;
use App\Models\URecipe;
use App\Models\User;
use Illuminate\Database\Seeder;

class URecipeSeeder extends Seeder
{
    private const E_MAILS = ['suke.shun.kato2@gmail.com', 'suke.shun.kato@gmail.com'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::E_MAILS as $email) {
            self::createRecipe($email, 0);
            self::createRecipe($email, 1);
            self::createRecipe($email, 2);
            self::createRecipe($email, fake()->numberBetween(3, 5));
        }
    }


    private static function createRecipe(string $userEmail, int $countImage, int $countUser = 1): void
    {
        $user = User::findByEmail($userEmail);

        // URecipeFactory を使って
        URecipe::factory()
            // $countUser 件数分のレコードのデータを作成する
            ->count($countUser)
            // BelongsToの部分のUserは、既に作成されたUserのidをURecipesのuser_idとして使用する
            ->for($user)
            // 多対多（BelongsToMany）の部分の URecipeImages, UImages のデータを作成する
            ->has(UImage::factory()
                ->count($countImage)
                ->state(function (array $attributes) use ($user) {
                    // $attributes は UImages のカラムの配列
                    // UImages の user_id を下記で上書きして作成する
                    return [
                        'user_id' => $user->id
                    ];
            }))
            ->create();
    }
}

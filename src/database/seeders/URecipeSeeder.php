<?php

namespace Database\Seeders;

use App\Models\URecipe;
use App\Models\User;
use Illuminate\Database\Seeder;

class URecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // URecipeFactory を使って5レコードデータを作成する
        URecipe::factory()
            ->count(5)
            ->for(User::findByEmail('suke.shun.kato2@gmail.com'))
            ->create();
        URecipe::factory()
            ->count(5)
            ->for(User::findByEmail('suke.shun.kato@gmail.com'))
            ->create();
    }
}

<?php

namespace Database\Seeders;

use App\Models\URecipe;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // RecipeFactory を使って10レコードデータを作成する
        URecipe::factory(10)->create();
    }
}

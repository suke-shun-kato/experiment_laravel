<?php

namespace Database\Seeders;

use App\Models\URecipe;
use Illuminate\Database\Seeder;

class URecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // URecipeFactory を使って10レコードデータを作成する
        URecipe::factory(10)->create();
    }
}

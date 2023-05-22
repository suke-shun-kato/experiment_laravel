<?php

namespace Database\Factories;

use App\Models\UImage;
use App\Models\URecipe;
use App\Models\URecipeImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<URecipeImage>
 */
class URecipeImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'u_recipe_id' => URecipe::factory(),
            'u_image_id' => UImage::factory()
        ];
    }
}

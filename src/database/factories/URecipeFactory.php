<?php

namespace Database\Factories;

use App\Models\URecipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\URecipe>
 */
class URecipeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = URecipe::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            '味噌汁',
            '大根サラダ',
            'オムレツ',
            'オムライス',
            '鯖の味噌煮',
            'カレーライス',
            'シチュー',
            '焼き鳥',
            '焼き肉',
            '蒸し野菜'
        ];


        return [
            // 下記は1回ごとにユーザーをファクトリで作成する
//            'user_id' => User::factory(),
            'user_id' => 1,
            'title' => fake()->unique()->randomElement($titles),
            'description' => fake()->text(100),
        ];
    }
}

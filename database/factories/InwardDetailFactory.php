<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\User;
use App\Models\Inward;
use App\Models\YarnType;
use App\Models\Item;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InwardDetail>
 */
class InwardDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->value('id'),
            'inward_id' => Inward::inRandomOrder()->value('id'),
            'yarn_type_id' => YarnType::inRandomOrder()->value('id'),
            'item_id' => Item::inRandomOrder()->value('id'),
            'yarn_dia' => fake()->randomDigit(),
            'yarn_gsm' => fake()->randomDigit(),
            'yarn_gauge' => fake()->unique()->sentence(10),
            'inward_qty' => fake()->randomDigit(),
            'inward_weight'=> fake()->randomFloat(1,1),
            'inward_detail_date' => fake()->dateTimeThisMonth()->format('Y-m-d'),
            'yarn_colour' => fake()->unique()->sentence(10),
        ];
    }
}

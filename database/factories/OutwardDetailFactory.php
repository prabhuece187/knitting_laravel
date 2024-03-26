<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Outward;
use App\Models\YarnType;
use App\Models\Item;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OutwardDetails>
 */
class OutwardDetailFactory extends Factory
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
            'outward_id' => Outward::inRandomOrder()->value('id'),
            'yarn_type_id' => YarnType::inRandomOrder()->value('id'),
            'item_id' => Item::inRandomOrder()->value('id'),
            'yarn_dia' => fake()->randomDigit(),
            'yarn_gsm' => fake()->randomDigit(),
            'yarn_gauge' => fake()->unique()->sentence(10),
            'outward_qty' => fake()->randomDigit(),
            'outward_weight'=> fake()->randomFloat(3,2),
            'deliverd_weight'=> fake()->randomFloat(3,2),
            'outward_detail_date' => fake()->dateTimeThisMonth()->format('Y-m-d'),
            'yarn_colour' => fake()->unique()->sentence(10),
        ];
    }
}

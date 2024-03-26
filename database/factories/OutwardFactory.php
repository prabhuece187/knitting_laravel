<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Customer;
use App\Models\Mill;
use App\Models\Inward;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Outward>
 */
class OutwardFactory extends Factory
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
            'customer_id' => Customer::inRandomOrder()->value('id'),
            'mill_id' => Mill::inRandomOrder()->value('id'),
            'inward_id' => Inward::inRandomOrder()->value('id'),
            'outward_no' => fake()->randomNumber(),
            'outward_invoice_no' => fake()->randomNumber(),
            'outward_tin_no' => fake()->unique()->sentence(10),
            'outward_date' => fake()->dateTimeThisMonth()->format('Y-m-d'),
            'total_weight'=> fake()->randomFloat(3,2),
            'total_quantity' => fake()->randomDigit(),
            'outward_vehicle_no' => fake()->unique()->sentence(12),
            'yarn_send' => fake()->unique()->sentence(12),
            'status' => true,
        ];
    }
}

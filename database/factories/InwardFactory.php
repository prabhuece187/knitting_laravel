<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\User;
use App\Models\Customer;
use App\Models\Mill;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inward>
 */
class InwardFactory extends Factory
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
            'inward_no' => fake()->randomNumber(),
            'inward_invoice_no' => fake()->randomNumber(),
            'inward_tin_no' => fake()->unique()->sentence(10),
            'inward_date' => fake()->dateTimeThisMonth()->format('Y-m-d'),
            'total_weight'=> fake()->randomFloat(1,1),
            'total_quantity' => fake()->randomDigit(),
            'inward_vehicle_no' => fake()->unique()->sentence(12),
            'status' => true,
        ];
    }
}

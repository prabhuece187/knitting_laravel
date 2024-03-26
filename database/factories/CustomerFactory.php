<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word;
        return [
            'customer_name' => $name,
            'customer_state' => fake()->unique()->word,
            'customer_gst_no' => fake()->unique()->sentence(15),
            'customer_mobile' => fake()->unique()->phoneNumber(),
            'customer_email' => fake()->unique()->safeEmail(),
            'user_id' => User::inRandomOrder()->value('id'),
            'customer_address' => fake()->paragraph(1,true),
        ];
    }
}

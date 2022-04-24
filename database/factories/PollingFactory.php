<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Polling>
 */
class PollingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'owner_id' => 'b1da09aef749df3d',
            'dir' => $this->faker->unique()->safeEmail(),
            'question' => $this->faker->text(50),
            'description' => $this->faker->text(100),
            'q_img' => null,
            'deadline' => null,
            'with_device_res' => 0,
            'with_password' => 0,
            'password' => null,
            'with_area_res' => 0,
            'req_email' => 0,
            'req_name' => 0,
        ];
    }
}

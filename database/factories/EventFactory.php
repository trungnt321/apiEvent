<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start_time = Carbon::now();
        $end_time = Carbon::parse($start_time)->addDays(7);
        return [
            'name' => fake()->name(),
            'date' => fake()->date(),
            'location' => fake()->unique()->address(),
            'contact'=>fake()->unique()->phoneNumber(),
            'status' => fake()->numberBetween(0,1),
            'user_id' => fake()->numberBetween(1,30),
            'start_time' =>$start_time,
            'end_time' =>$end_time,
            ];
    }
}

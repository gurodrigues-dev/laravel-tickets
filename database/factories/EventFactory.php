<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'event_date' => fake()->dateTimeBetween('+1 week', '+6 months'),
            'total_tickets' => fake()->numberBetween(50, 500),
            'available_tickets' => fake()->numberBetween(50, 500),
            'version' => 1,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriberFactory extends Factory
{
    protected $model = Subscriber::class;

    public function definition()
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'status' => 'active',
        ];
    }

    public function unsubscribed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'unsubscribed',
            ];
        });
    }
}
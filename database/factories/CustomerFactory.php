<?php

namespace Freemius\Laravel\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Freemius\Laravel\Customer;

class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'billable_id' => rand(1, 1000),
            'billable_type' => 'App\\Models\\User',
            'freemius_id' => rand(1, 1000),
            'trial_ends_at' => null,
        ];
    }
}

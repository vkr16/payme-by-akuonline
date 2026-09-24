<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserBank;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserBank>
 */
class UserBankFactory extends Factory
{
    protected $model = UserBank::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bank_name' => fake()->randomElement(['BCA', 'Mandiri', 'BRI', 'BNI', 'GoPay', 'Dana', 'OVO']),
            'account_number' => fake()->numerify('##########'),
            'account_holder' => fake()->name(),
            'is_default' => false,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->e164PhoneNumber(),
            'password' => static::$password ??= Hash::make('password'),
            'avatar' => null,
            'is_active' => true,
            'last_login_at' => null,
            'roles' => User::ROLE_USER,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's role is admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'roles' => User::ROLE_ADMIN,
        ]);
    }
}

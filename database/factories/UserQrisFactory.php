<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserQris;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserQris>
 */
class UserQrisFactory extends Factory
{
    protected $model = UserQris::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'merchant_name' => fake()->company(),
            'merchant_city' => fake()->city(),
            'payload' => '00020101021126580014ID.LINKAJA.WWW01189360000000000000000208123456785204549953033605802ID5914'.fake()->company().'6007JAKARTA6304A1B2',
            'image_path' => null,
            'is_default' => false,
        ];
    }
}

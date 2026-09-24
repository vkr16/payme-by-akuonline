<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Bill>
 */
class BillFactory extends Factory
{
    protected $model = Bill::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'slug' => Str::random(10),
            'title' => fake()->sentence(3),
            'qris_payload' => '00020101021126580014ID.LINKAJA.WWW01189360000000000000000208123456785204549953033605802ID5910WARUNG KOPI6007JAKARTA6304A1B2',
            'qris_merchant_name' => 'WARUNG KOPI',
            'qris_merchant_city' => 'JAKARTA',
            'qris_image_path' => null,
            'delivery_fee' => 0,
            'service_fee' => 0,
            'discount' => 0,
            'receipt_image_path' => null,
            'status' => 'active',
        ];
    }
}

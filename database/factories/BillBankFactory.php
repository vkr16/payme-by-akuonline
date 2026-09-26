<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\BillBank;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillBank>
 */
class BillBankFactory extends Factory
{
    protected $model = BillBank::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bill_id' => Bill::factory(),
            'bank_name' => fake()->randomElement(['BCA', 'Mandiri', 'BRI', 'BNI', 'GoPay', 'Dana', 'OVO']),
            'account_number' => fake()->numerify('##########'),
            'account_holder' => fake()->name(),
            'is_primary' => false,
        ];
    }
}

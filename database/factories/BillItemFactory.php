<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\BillItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillItem>
 */
class BillItemFactory extends Factory
{
    protected $model = BillItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bill_id' => Bill::factory(),
            'name' => fake()->words(2, true),
            'qty' => fake()->numberBetween(1, 4),
            'price' => fake()->numberBetween(10, 80) * 1000,
        ];
    }
}

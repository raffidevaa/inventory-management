<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $stock = $this->faker->numberBetween(5, 50);
        $stockAvailable = $this->faker->numberBetween(0, $stock);

        return [
            'code' => 'ITM-' . strtoupper($this->faker->unique()->bothify('???-####')),
            'name' => $this->faker->words(3, true),
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),
            'stock' => $stock,
            'stock_available' => $stockAvailable,
            'location' => 'Gedung ' . $this->faker->randomElement(['A', 'B', 'C']) . ', Lantai ' . $this->faker->numberBetween(1, 5),
            'condition' => $this->faker->randomElement(['good', 'good', 'good', 'lightly_damaged']),
            'image' => null,
        ];
    }
}

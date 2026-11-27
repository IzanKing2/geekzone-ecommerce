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
        return [
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 5, 200),
            'stock' => 10,
            'featured' => false,
            'image_url' => 'img/products/placeholder.jpg',
            'category_id' => Category::factory(),
        ];
    }

    /** Producto sin stock. */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => ['stock' => 0]);
    }

    /** Producto destacado. */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => ['featured' => true]);
    }
}

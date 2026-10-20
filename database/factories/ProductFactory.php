<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Product;
use App\Modules\Catalog\Enums\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'company_id' => Company::factory(),
            'category' => fake()->randomElement(ProductCategory::values()),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(15, 90) * 1000,
            'tags' => [],
            'prerequisites' => [],
            'is_featured' => false,
            'is_unavailable' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function soldOut(): static
    {
        return $this->state(fn () => ['is_unavailable' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProductOrigin;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

// modify by claude
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
        return [
            'product_category_id' => ProductCategory::factory(),
            'name_fr' => fake()->words(3, true),
            'name_en' => fake()->words(3, true),
            'origin' => fake()->randomElement(ProductOrigin::cases()),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

// modify by claude
/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'unit_id' => Unit::factory(),
            'packaging_type_id' => null,
            'sku' => fake()->unique()->bothify('SKU-####??'),
            'retail_price' => fake()->randomFloat(2, 1, 100),
            'wholesale_price' => null,
            'wholesale_min_qty' => null,
            'is_active' => true,
        ];
    }
}

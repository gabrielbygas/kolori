<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

// modify by claude
/**
 * @extends Factory<ProductCategory>
 */
class ProductCategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nameFr = fake()->unique()->words(2, true);

        return [
            'name_fr' => $nameFr,
            'name_en' => fake()->words(2, true),
            'slug' => Str::slug($nameFr),
        ];
    }
}

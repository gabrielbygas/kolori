<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PackagingType;
use Illuminate\Database\Eloquent\Factories\Factory;

// modify by claude
/**
 * @extends Factory<PackagingType>
 */
class PackagingTypeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->lexify('packaging-????'),
            'name_fr' => fake()->words(2, true),
            'name_en' => fake()->words(2, true),
        ];
    }
}

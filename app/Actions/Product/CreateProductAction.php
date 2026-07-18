<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

// modify by claude
class CreateProductAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(array $data): Product
    {
        return DB::transaction(function () use ($data): Product {
            $product = Product::create([
                'product_category_id' => $data['product_category_id'],
                'name_fr' => $data['name_fr'],
                'name_en' => $data['name_en'],
                'origin' => $data['origin'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            foreach ($data['variants'] as $variant) {
                $product->variants()->create([
                    'unit_id' => $variant['unit_id'],
                    'packaging_type_id' => $variant['packaging_type_id'] ?? null,
                    'sku' => $variant['sku'] ?? null,
                    'retail_price' => $variant['retail_price'],
                    'wholesale_price' => $variant['wholesale_price'] ?? null,
                    'wholesale_min_qty' => $variant['wholesale_min_qty'] ?? null,
                    'is_active' => $variant['is_active'] ?? true,
                ]);
            }

            return $product;
        });
    }
}

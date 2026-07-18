<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

// modify by claude
class UpdateProductAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(Product $product, array $data): Product
    {
        DB::transaction(function () use ($product, $data): void {
            $product->update([
                'product_category_id' => $data['product_category_id'],
                'name_fr' => $data['name_fr'],
                'name_en' => $data['name_en'],
                'origin' => $data['origin'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $submittedIds = [];

            foreach ($data['variants'] as $variant) {
                $attributes = [
                    'unit_id' => $variant['unit_id'],
                    'packaging_type_id' => $variant['packaging_type_id'] ?? null,
                    'sku' => $variant['sku'] ?? null,
                    'retail_price' => $variant['retail_price'],
                    'wholesale_price' => $variant['wholesale_price'] ?? null,
                    'wholesale_min_qty' => $variant['wholesale_min_qty'] ?? null,
                    'is_active' => $variant['is_active'] ?? true,
                ];

                if (! empty($variant['id'])) {
                    $product->variants()->whereKey($variant['id'])->update($attributes);
                    $submittedIds[] = $variant['id'];
                } else {
                    $submittedIds[] = $product->variants()->create($attributes)->id;
                }
            }

            $product->variants()->whereKeyNot($submittedIds)->delete();
        });

        return $product->fresh('variants');
    }
}

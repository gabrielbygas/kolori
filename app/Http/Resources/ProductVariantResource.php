<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// modify by claude
class ProductVariantResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'unit_id' => $this->unit_id,
            'packaging_type_id' => $this->packaging_type_id,
            'sku' => $this->sku,
            'retail_price' => $this->retail_price,
            'wholesale_price' => $this->wholesale_price,
            'wholesale_min_qty' => $this->wholesale_min_qty,
            'low_stock_threshold' => $this->low_stock_threshold,
            'current_stock' => $this->current_stock,
            'is_low_stock' => $this->isLowStock(),
            'is_active' => $this->is_active,
            'unit' => UnitResource::make($this->whenLoaded('unit')),
            'packaging_type' => PackagingTypeResource::make($this->whenLoaded('packagingType')),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// modify by claude
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_category_id' => $this->product_category_id,
            'name_fr' => $this->name_fr,
            'name_en' => $this->name_en,
            'origin' => $this->origin,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'category' => ProductCategoryResource::make($this->whenLoaded('category')),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// modify by claude
class SaleItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'label' => $this->variantLabel(),
            'quantity' => $this->quantity,
            'pricing_tier' => $this->pricing_tier,
            'unit_price' => $this->unit_price,
            'subtotal' => $this->subtotal,
        ];
    }

    private function variantLabel(): string
    {
        $variant = $this->productVariant;
        $label = $variant->product->name_fr.' — '.$variant->unit->name_fr;

        if ($variant->packagingType) {
            $label .= ' / '.$variant->packagingType->name_fr;
        }

        return $label;
    }
}

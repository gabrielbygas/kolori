<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// modify by claude
class StockMovementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'reason' => $this->reason,
            'variant_label' => $this->variantLabel(),
            'user_name' => $this->user?->name,
            'created_at' => $this->created_at->format('d/m/Y H:i'),
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

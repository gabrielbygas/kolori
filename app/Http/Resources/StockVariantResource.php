<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// modify by claude
class StockVariantResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->variantLabel(),
            'current_stock' => $this->current_stock,
            'low_stock_threshold' => $this->low_stock_threshold,
            'is_low_stock' => $this->isLowStock(),
        ];
    }

    private function variantLabel(): string
    {
        $label = $this->product->name_fr.' — '.$this->unit->name_fr;

        if ($this->packagingType) {
            $label .= ' / '.$this->packagingType->name_fr;
        }

        return $label;
    }
}

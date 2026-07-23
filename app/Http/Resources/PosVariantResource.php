<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// modify by claude
class PosVariantResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->variantLabel(),
            'retail_price' => $this->retail_price,
            'wholesale_price' => $this->wholesale_price,
            'wholesale_min_qty' => $this->wholesale_min_qty,
            'current_stock' => $this->current_stock,
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

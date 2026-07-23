<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// modify by claude
class SaleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at->format('d/m/Y H:i'),
            'user_name' => $this->user->name,
            'total_usd' => $this->total_usd,
            'exchange_rate' => $this->exchange_rate,
            'total_cdf' => $this->total_cdf,
            'payment_currency' => $this->payment_currency,
            'amount_tendered' => $this->amount_tendered,
            'change_due' => $this->change_due,
            'items' => SaleItemResource::collection($this->whenLoaded('items')),
        ];
    }
}

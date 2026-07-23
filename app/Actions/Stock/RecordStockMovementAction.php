<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Enums\StockMovementType;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// modify by claude
class RecordStockMovementAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(array $data): StockMovement
    {
        return DB::transaction(function () use ($data): StockMovement {
            $variant = ProductVariant::query()
                ->lockForUpdate()
                ->findOrFail($data['product_variant_id']);

            $type = $data['type'] instanceof StockMovementType
                ? $data['type']
                : StockMovementType::from($data['type']);

            $delta = $type === StockMovementType::Out ? -$data['quantity'] : $data['quantity'];
            $newStock = bcadd((string) $variant->current_stock, (string) $delta, 2);

            if (bccomp($newStock, '0', 2) < 0) {
                throw ValidationException::withMessages([
                    'quantity' => "Stock insuffisant : il reste {$variant->current_stock} en stock.",
                ]);
            }

            $movement = $variant->stockMovements()->create([
                'type' => $type,
                'quantity' => $data['quantity'],
                'reason' => $data['reason'] ?? null,
                'user_id' => $data['user_id'] ?? null,
                'sale_id' => $data['sale_id'] ?? null,
            ]);

            // current_stock est volontairement hors du $fillable de ProductVariant
            // (voir le modèle) : forceFill() est le seul moyen légitime de le
            // modifier, réservé à cette Action.
            $variant->forceFill(['current_stock' => $newStock])->save();

            return $movement;
        });
    }
}

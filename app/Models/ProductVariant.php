<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// modify by claude
#[Fillable([
    'product_id',
    'unit_id',
    'packaging_type_id',
    'sku',
    'retail_price',
    'wholesale_price',
    'wholesale_min_qty',
    'low_stock_threshold',
    'is_active',
])]
class ProductVariant extends BaseModel
{
    /**
     * `current_stock` volontairement absent du fillable : ne doit jamais
     * être modifié en masse, uniquement via RecordStockMovementAction.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'retail_price' => 'decimal:2',
            'wholesale_price' => 'decimal:2',
            'wholesale_min_qty' => 'integer',
            'current_stock' => 'decimal:2',
            'low_stock_threshold' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Vrai si un seuil est configuré et que le stock actuel est à ou sous ce seuil.
     */
    public function isLowStock(): bool
    {
        return $this->low_stock_threshold !== null
            && bccomp((string) $this->current_stock, (string) $this->low_stock_threshold, 2) <= 0;
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * @return BelongsTo<PackagingType, $this>
     */
    public function packagingType(): BelongsTo
    {
        return $this->belongsTo(PackagingType::class);
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}

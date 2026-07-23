<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentCurrency;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// modify by claude
#[Fillable([
    'user_id',
    'total_usd',
    'exchange_rate',
    'total_cdf',
    'payment_currency',
    'amount_tendered',
    'change_due',
])]
class Sale extends BaseModel
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_usd' => 'decimal:2',
            'exchange_rate' => 'decimal:4',
            'total_cdf' => 'decimal:2',
            'payment_currency' => PaymentCurrency::class,
            'amount_tendered' => 'decimal:2',
            'change_due' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<SaleItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}

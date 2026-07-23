<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Actions\Stock\RecordStockMovementAction;
use App\Enums\PricingTier;
use App\Enums\StockMovementType;
use App\Models\ProductVariant;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// modify by claude
class CreateSaleAction
{
    public function __construct(
        private readonly RecordStockMovementAction $recordStockMovement,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(array $data): Sale
    {
        return DB::transaction(function () use ($data): Sale {
            $pricingMode = config('kolori.pricing_mode');
            $exchangeRate = (string) config('kolori.exchange_rate');

            $lines = [];
            $totalUsd = '0';

            foreach ($data['items'] as $item) {
                $variant = ProductVariant::query()->findOrFail($item['product_variant_id']);

                $tier = $this->resolveTier($variant, $item, $pricingMode);
                $unitPrice = (string) ($tier === PricingTier::Wholesale ? $variant->wholesale_price : $variant->retail_price);
                $subtotal = bcmul((string) $item['quantity'], $unitPrice, 2);

                $totalUsd = bcadd($totalUsd, $subtotal, 2);

                $lines[] = [
                    'product_variant_id' => $variant->id,
                    'quantity' => $item['quantity'],
                    'pricing_tier' => $tier,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }

            // Le CDF n'a pas de sous-unité utilisée en pratique : le total est
            // arrondi à l'entier le plus proche, contrairement à l'USD (base réelle).
            $totalCdf = (string) round((float) bcmul($totalUsd, $exchangeRate, 4));

            $amountDue = $data['payment_currency'] === 'cdf' ? $totalCdf : $totalUsd;
            $changeDue = bcsub((string) $data['amount_tendered'], $amountDue, 2);

            if (bccomp($changeDue, '0', 2) < 0) {
                throw ValidationException::withMessages([
                    'amount_tendered' => 'Le montant reçu est insuffisant pour couvrir le total de la vente.',
                ]);
            }

            $sale = Sale::create([
                'user_id' => $data['user_id'],
                'total_usd' => $totalUsd,
                'exchange_rate' => $exchangeRate,
                'total_cdf' => $totalCdf,
                'payment_currency' => $data['payment_currency'],
                'amount_tendered' => $data['amount_tendered'],
                'change_due' => $changeDue,
            ]);

            foreach ($lines as $index => $line) {
                $sale->items()->create($line);

                // Réutilise directement le refus de stock négatif du point 5 :
                // une vente qui dépasse le stock disponible est rejetée dans
                // son intégralité (transaction annulée, aucune vente partielle).
                try {
                    ($this->recordStockMovement)([
                        'product_variant_id' => $line['product_variant_id'],
                        'sale_id' => $sale->id,
                        'type' => StockMovementType::Out,
                        'quantity' => $line['quantity'],
                        'reason' => 'Vente',
                        'user_id' => $data['user_id'],
                    ]);
                } catch (ValidationException $e) {
                    // Réindexe l'erreur générique de l'Action de stock ("quantity")
                    // vers la ligne de panier concernée, pour que le formulaire
                    // POS puisse l'afficher au bon endroit.
                    throw ValidationException::withMessages([
                        "items.{$index}.quantity" => $e->errors()['quantity'][0],
                    ]);
                }
            }

            return $sale->load('items.productVariant');
        });
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function resolveTier(ProductVariant $variant, array $item, string $pricingMode): PricingTier
    {
        $suggested = $this->suggestedTier($variant, (float) $item['quantity']);

        if ($pricingMode !== 'manual') {
            return $suggested;
        }

        $requested = isset($item['pricing_tier'])
            ? PricingTier::from($item['pricing_tier'])
            : $suggested;

        // Pas de prix de gros défini sur cette variante : impossible d'honorer
        // une demande de tarif de gros, on retombe sur le détail.
        if ($requested === PricingTier::Wholesale && $variant->wholesale_price === null) {
            return PricingTier::Retail;
        }

        return $requested;
    }

    private function suggestedTier(ProductVariant $variant, float $quantity): PricingTier
    {
        if ($variant->wholesale_price !== null
            && $variant->wholesale_min_qty !== null
            && $quantity >= $variant->wholesale_min_qty) {
            return PricingTier::Wholesale;
        }

        return PricingTier::Retail;
    }
}

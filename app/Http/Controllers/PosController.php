<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Sale\CreateSaleAction;
use App\Http\Requests\Sale\StoreSaleRequest;
use App\Http\Resources\PosVariantResource;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

// modify by claude
class PosController extends Controller
{
    public function index(): Response
    {
        $variants = ProductVariant::query()
            ->with(['product', 'unit', 'packagingType'])
            ->whereHas('product', fn ($query) => $query->where('is_active', true))
            ->where('product_variants.is_active', true)
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->orderBy('products.name_fr')
            ->select('product_variants.*')
            ->get();

        return Inertia::render('Pos/Index', [
            'variants' => PosVariantResource::collection($variants),
            'exchangeRate' => (float) config('kolori.exchange_rate'),
            'pricingMode' => config('kolori.pricing_mode'),
        ]);
    }

    public function store(StoreSaleRequest $request, CreateSaleAction $createSale): RedirectResponse
    {
        $sale = $createSale([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('sales.receipt', $sale);
    }
}

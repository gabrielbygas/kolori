<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Stock\RecordStockMovementAction;
use App\Http\Requests\Stock\StoreStockMovementRequest;
use App\Http\Resources\StockMovementResource;
use App\Http\Resources\StockVariantResource;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

// modify by claude
class StockMovementController extends Controller
{
    public function index(): Response
    {
        $variants = ProductVariant::query()
            ->with(['product', 'unit', 'packagingType'])
            ->whereHas('product', fn ($query) => $query->where('is_active', true))
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->orderBy('products.name_fr')
            ->select('product_variants.*')
            ->get();

        $recentMovements = StockMovement::query()
            ->with(['productVariant.product', 'productVariant.unit', 'productVariant.packagingType', 'user'])
            ->latest()
            ->limit(20)
            ->get();

        return Inertia::render('Stock/Index', [
            'variants' => StockVariantResource::collection($variants),
            'recentMovements' => StockMovementResource::collection($recentMovements),
        ]);
    }

    public function store(StoreStockMovementRequest $request, RecordStockMovementAction $recordMovement): RedirectResponse
    {
        $recordMovement([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('stock.index')->with('success', 'Mouvement enregistré.');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Product\CreateProductAction;
use App\Actions\Product\UpdateProductAction;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\PackagingTypeResource;
use App\Http\Resources\ProductCategoryResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\UnitResource;
use App\Models\PackagingType;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

// modify by claude
class ProductController extends Controller
{
    public function index(): Response
    {
        $products = Product::query()
            ->with(['category', 'variants.unit', 'variants.packagingType'])
            ->orderBy('name_fr')
            ->paginate(20)
            ->through(fn (Product $product) => new ProductResource($product));

        return Inertia::render('Products/Index', [
            'products' => $products,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Products/Create', [
            'categories' => ProductCategoryResource::collection(
                ProductCategory::query()->orderBy('name_fr')->get()
            ),
            'units' => UnitResource::collection(Unit::query()->orderBy('name_fr')->get()),
            'packagingTypes' => PackagingTypeResource::collection(
                PackagingType::query()->orderBy('name_fr')->get()
            ),
        ]);
    }

    public function store(StoreProductRequest $request, CreateProductAction $createProduct): RedirectResponse
    {
        $createProduct($request->validated());

        return redirect()->route('products.index')->with('success', 'Produit créé.');
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('Products/Edit', [
            'product' => new ProductResource($product->load('variants')),
            'categories' => ProductCategoryResource::collection(
                ProductCategory::query()->orderBy('name_fr')->get()
            ),
            'units' => UnitResource::collection(Unit::query()->orderBy('name_fr')->get()),
            'packagingTypes' => PackagingTypeResource::collection(
                PackagingType::query()->orderBy('name_fr')->get()
            ),
        ]);
    }

    public function update(
        UpdateProductRequest $request,
        Product $product,
        UpdateProductAction $updateProduct
    ): RedirectResponse {
        $updateProduct($product, $request->validated());

        return redirect()->route('products.index')->with('success', 'Produit mis à jour.');
    }
}

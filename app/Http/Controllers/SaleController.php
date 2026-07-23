<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\SaleResource;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

// modify by claude
class SaleController extends Controller
{
    public function receipt(Sale $sale): Response
    {
        $sale->load(['items.productVariant.product', 'items.productVariant.unit', 'items.productVariant.packagingType', 'user']);

        return Inertia::render('Sales/Receipt', [
            'sale' => new SaleResource($sale),
        ]);
    }

    public function receiptPdf(Sale $sale): HttpResponse
    {
        $sale->load(['items.productVariant.product', 'items.productVariant.unit', 'items.productVariant.packagingType', 'user']);

        $pdf = Pdf::loadView('receipts.sale', ['sale' => $sale]);

        return $pdf->download("recu-{$sale->id}.pdf");
    }
}

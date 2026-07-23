<!DOCTYPE html>
{{-- modify by claude --}}
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Reçu {{ $sale->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .muted { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { padding: 4px 6px; text-align: left; }
        th { border-bottom: 1px solid #d1d5db; font-size: 11px; text-transform: uppercase; }
        td { border-bottom: 1px solid #f3f4f6; }
        .text-right { text-align: right; }
        .totals { width: 60%; margin-left: auto; margin-top: 12px; }
        .totals td { border: none; padding: 2px 6px; }
        .grand-total { font-size: 15px; font-weight: bold; }
        .footer { margin-top: 20px; font-size: 10px; color: #6b7280; text-align: center; }
    </style>
</head>
<body>
    <h1>{{ config('app.name') }}</h1>
    <p class="muted">Reçu de vente — {{ $sale->created_at->format('d/m/Y H:i') }}</p>
    <p class="muted">Vendeur : {{ $sale->user->name }} — Réf. {{ $sale->id }}</p>

    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th class="text-right">Qté</th>
                <th class="text-right">PU (USD)</th>
                <th class="text-right">Sous-total (USD)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
                <tr>
                    <td>
                        {{ $item->productVariant->product->name_fr }} — {{ $item->productVariant->unit->name_fr }}
                        @if ($item->productVariant->packagingType)
                            / {{ $item->productVariant->packagingType->name_fr }}
                        @endif
                        @if ($item->pricing_tier->value === 'wholesale')
                            (gros)
                        @endif
                    </td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format((float) $item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Total USD</td>
            <td class="text-right grand-total">${{ number_format((float) $sale->total_usd, 2) }}</td>
        </tr>
        <tr>
            <td>Total CDF</td>
            <td class="text-right grand-total">{{ number_format((float) $sale->total_cdf, 0, ',', ' ') }} CDF</td>
        </tr>
        <tr>
            <td>Devise reçue</td>
            <td class="text-right">{{ strtoupper($sale->payment_currency->value) }}</td>
        </tr>
        <tr>
            <td>Montant reçu</td>
            <td class="text-right">{{ number_format((float) $sale->amount_tendered, 2) }}</td>
        </tr>
        <tr>
            <td>Monnaie rendue</td>
            <td class="text-right">{{ number_format((float) $sale->change_due, 2) }}</td>
        </tr>
    </table>

    <p class="footer">
        Taux appliqué : 1 USD = {{ number_format((float) $sale->exchange_rate, 2) }} CDF — Merci de votre achat.
    </p>
</body>
</html>

<?php

declare(strict_types=1);

namespace App\Http\Requests\Sale;

use App\Enums\PaymentCurrency;
use App\Enums\PricingTier;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// modify by claude
class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'uuid', Rule::exists('product_variants', 'id')],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.pricing_tier' => ['nullable', Rule::enum(PricingTier::class)],
            'payment_currency' => ['required', Rule::enum(PaymentCurrency::class)],
            'amount_tendered' => ['required', 'numeric', 'min:0'],
        ];
    }
}

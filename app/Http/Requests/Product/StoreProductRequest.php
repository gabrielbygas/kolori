<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\Enums\ProductOrigin;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

// modify by claude
class StoreProductRequest extends FormRequest
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
            'product_category_id' => ['required', 'uuid', Rule::exists('product_categories', 'id')],
            'name_fr' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'origin' => ['required', Rule::enum(ProductOrigin::class)],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.unit_id' => ['required', 'uuid', Rule::exists('units', 'id')],
            'variants.*.packaging_type_id' => ['nullable', 'uuid', Rule::exists('packaging_types', 'id')],
            'variants.*.sku' => ['nullable', 'string', 'max:255'],
            'variants.*.retail_price' => ['required', 'numeric', 'min:0'],
            'variants.*.wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.wholesale_min_qty' => ['nullable', 'integer', 'min:1'],
            'variants.*.low_stock_threshold' => ['nullable', 'numeric', 'min:0'],
            'variants.*.is_active' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $seen = [];

            foreach ($this->input('variants', []) as $index => $variant) {
                $key = ($variant['unit_id'] ?? '').'|'.($variant['packaging_type_id'] ?? '');

                if (isset($seen[$key])) {
                    $validator->errors()->add(
                        "variants.{$index}.unit_id",
                        'Cette combinaison unité/emballage est déjà utilisée par une autre variante de ce produit.'
                    );
                }

                $seen[$key] = true;
            }
        });
    }
}

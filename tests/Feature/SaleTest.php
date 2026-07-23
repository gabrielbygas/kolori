<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

// modify by claude
class SaleTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role, 'web');

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_admin_can_create_a_sale_at_retail_price_below_wholesale_threshold(): void
    {
        $admin = $this->userWithRole('admin');
        $variant = ProductVariant::factory()->create([
            'retail_price' => 15,
            'wholesale_price' => 10,
            'wholesale_min_qty' => 5,
            'current_stock' => 50,
        ]);

        $response = $this->actingAs($admin)->post('/pos', [
            'items' => [['product_variant_id' => $variant->id, 'quantity' => 2]],
            'payment_currency' => 'usd',
            'amount_tendered' => 50,
        ]);

        $response->assertSessionHasNoErrors();

        $sale = Sale::latest()->firstOrFail();
        $this->assertSame('30.00', $sale->total_usd);
        $this->assertSame('retail', $sale->items->first()->pricing_tier->value);
        $this->assertSame('20.00', $sale->change_due);
        $this->assertSame('48.00', $variant->fresh()->current_stock);
    }

    public function test_wholesale_price_applies_automatically_at_the_threshold(): void
    {
        config(['kolori.pricing_mode' => 'automatic']);
        $admin = $this->userWithRole('admin');
        $variant = ProductVariant::factory()->create([
            'retail_price' => 15,
            'wholesale_price' => 10,
            'wholesale_min_qty' => 5,
            'current_stock' => 50,
        ]);

        $this->actingAs($admin)->post('/pos', [
            'items' => [['product_variant_id' => $variant->id, 'quantity' => 5]],
            'payment_currency' => 'usd',
            'amount_tendered' => 50,
        ])->assertSessionHasNoErrors();

        $item = Sale::latest()->firstOrFail()->items->first();
        $this->assertSame('wholesale', $item->pricing_tier->value);
        $this->assertSame('10.00', $item->unit_price);
    }

    public function test_manual_pricing_mode_respects_the_vendors_choice(): void
    {
        config(['kolori.pricing_mode' => 'manual']);
        $admin = $this->userWithRole('admin');
        $variant = ProductVariant::factory()->create([
            'retail_price' => 15,
            'wholesale_price' => 10,
            'wholesale_min_qty' => 5,
            'current_stock' => 50,
        ]);

        // Quantité sous le seuil (2 < 5) mais le vendeur force le tarif de gros.
        $this->actingAs($admin)->post('/pos', [
            'items' => [['product_variant_id' => $variant->id, 'quantity' => 2, 'pricing_tier' => 'wholesale']],
            'payment_currency' => 'usd',
            'amount_tendered' => 50,
        ])->assertSessionHasNoErrors();

        $item = Sale::latest()->firstOrFail()->items->first();
        $this->assertSame('wholesale', $item->pricing_tier->value);
        $this->assertSame('10.00', $item->unit_price);
    }

    public function test_total_in_cdf_uses_the_configured_exchange_rate(): void
    {
        config(['kolori.exchange_rate' => 2500]);
        $admin = $this->userWithRole('admin');
        $variant = ProductVariant::factory()->create(['retail_price' => 10, 'current_stock' => 50]);

        $this->actingAs($admin)->post('/pos', [
            'items' => [['product_variant_id' => $variant->id, 'quantity' => 1]],
            'payment_currency' => 'cdf',
            'amount_tendered' => 30000,
        ])->assertSessionHasNoErrors();

        $sale = Sale::latest()->firstOrFail();
        $this->assertSame('2500.0000', $sale->exchange_rate);
        $this->assertSame('25000.00', $sale->total_cdf);
        $this->assertSame('cdf', $sale->payment_currency->value);
        $this->assertSame('5000.00', $sale->change_due);
    }

    public function test_sale_is_rejected_when_amount_tendered_is_insufficient(): void
    {
        $admin = $this->userWithRole('admin');
        $variant = ProductVariant::factory()->create(['retail_price' => 15, 'current_stock' => 50]);

        $response = $this->actingAs($admin)->post('/pos', [
            'items' => [['product_variant_id' => $variant->id, 'quantity' => 1]],
            'payment_currency' => 'usd',
            'amount_tendered' => 5,
        ]);

        $response->assertSessionHasErrors('amount_tendered');
        $this->assertSame(0, Sale::count());
        $this->assertSame('50.00', $variant->fresh()->current_stock);
    }

    public function test_sale_is_rejected_when_stock_is_insufficient(): void
    {
        $admin = $this->userWithRole('admin');
        $variant = ProductVariant::factory()->create(['retail_price' => 15, 'current_stock' => 3]);

        $response = $this->actingAs($admin)->post('/pos', [
            'items' => [['product_variant_id' => $variant->id, 'quantity' => 10]],
            'payment_currency' => 'usd',
            'amount_tendered' => 500,
        ]);

        $response->assertSessionHasErrors('items.0.quantity');
        $this->assertSame(0, Sale::count());
        $this->assertSame(0, StockMovement::count());
        $this->assertSame('3.00', $variant->fresh()->current_stock);
    }

    public function test_vendeur_can_create_a_sale(): void
    {
        $vendeur = $this->userWithRole('vendeur');
        $variant = ProductVariant::factory()->create(['retail_price' => 5, 'current_stock' => 10]);

        $this->actingAs($vendeur)->post('/pos', [
            'items' => [['product_variant_id' => $variant->id, 'quantity' => 1]],
            'payment_currency' => 'usd',
            'amount_tendered' => 5,
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, Sale::count());
    }

    public function test_logisticien_cannot_access_the_pos(): void
    {
        $logisticien = $this->userWithRole('logisticien');
        $variant = ProductVariant::factory()->create();

        $this->actingAs($logisticien)->get('/pos')->assertForbidden();
        $this->actingAs($logisticien)->post('/pos', [
            'items' => [['product_variant_id' => $variant->id, 'quantity' => 1]],
            'payment_currency' => 'usd',
            'amount_tendered' => 100,
        ])->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/pos')->assertRedirect('/login');
    }

    public function test_receipt_is_accessible_to_admin_and_vendeur_but_not_logisticien(): void
    {
        $admin = $this->userWithRole('admin');
        $vendeur = $this->userWithRole('vendeur');
        $logisticien = $this->userWithRole('logisticien');
        $variant = ProductVariant::factory()->create(['retail_price' => 10, 'current_stock' => 10]);

        $this->actingAs($admin)->post('/pos', [
            'items' => [['product_variant_id' => $variant->id, 'quantity' => 1]],
            'payment_currency' => 'usd',
            'amount_tendered' => 10,
        ]);
        $sale = Sale::latest()->firstOrFail();

        $this->actingAs($admin)->get("/sales/{$sale->id}/receipt")->assertOk();
        $this->actingAs($vendeur)->get("/sales/{$sale->id}/receipt")->assertOk();
        $this->actingAs($logisticien)->get("/sales/{$sale->id}/receipt")->assertForbidden();
    }

    public function test_receipt_pdf_download_returns_a_pdf(): void
    {
        $admin = $this->userWithRole('admin');
        $variant = ProductVariant::factory()->create(['retail_price' => 10, 'current_stock' => 10]);

        $this->actingAs($admin)->post('/pos', [
            'items' => [['product_variant_id' => $variant->id, 'quantity' => 1]],
            'payment_currency' => 'usd',
            'amount_tendered' => 10,
        ]);
        $sale = Sale::latest()->firstOrFail();

        $response = $this->actingAs($admin)->get("/sales/{$sale->id}/receipt.pdf");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }
}

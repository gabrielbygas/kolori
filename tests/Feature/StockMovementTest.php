<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

// modify by claude
class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role, 'web');

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_admin_can_record_an_in_movement_and_stock_increases(): void
    {
        $admin = $this->userWithRole('admin');
        $variant = ProductVariant::factory()->create(['current_stock' => 10]);

        $response = $this->actingAs($admin)->post('/stock', [
            'product_variant_id' => $variant->id,
            'type' => 'in',
            'quantity' => 25,
            'reason' => 'Réception fournisseur',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/stock');
        $this->assertSame('35.00', $variant->fresh()->current_stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_variant_id' => $variant->id,
            'type' => 'in',
            'quantity' => 25,
            'reason' => 'Réception fournisseur',
            'user_id' => $admin->id,
        ]);
    }

    public function test_admin_can_record_an_out_movement_and_stock_decreases(): void
    {
        $admin = $this->userWithRole('admin');
        $variant = ProductVariant::factory()->create(['current_stock' => 30]);

        $response = $this->actingAs($admin)->post('/stock', [
            'product_variant_id' => $variant->id,
            'type' => 'out',
            'quantity' => 12,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/stock');
        $this->assertSame('18.00', $variant->fresh()->current_stock);
    }

    public function test_logisticien_can_record_a_movement(): void
    {
        $logisticien = $this->userWithRole('logisticien');
        $variant = ProductVariant::factory()->create(['current_stock' => 0]);

        $response = $this->actingAs($logisticien)->post('/stock', [
            'product_variant_id' => $variant->id,
            'type' => 'in',
            'quantity' => 5,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/stock');
        $this->assertSame('5.00', $variant->fresh()->current_stock);
    }

    public function test_out_movement_is_rejected_when_it_would_make_stock_negative(): void
    {
        $admin = $this->userWithRole('admin');
        $variant = ProductVariant::factory()->create(['current_stock' => 10]);

        $response = $this->actingAs($admin)->post('/stock', [
            'product_variant_id' => $variant->id,
            'type' => 'out',
            'quantity' => 50,
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertSame('10.00', $variant->fresh()->current_stock);
        $this->assertSame(0, StockMovement::count());
    }

    public function test_vendeur_cannot_access_stock_page(): void
    {
        $vendeur = $this->userWithRole('vendeur');

        $this->actingAs($vendeur)->get('/stock')->assertForbidden();
        $this->actingAs($vendeur)->post('/stock', [
            'product_variant_id' => ProductVariant::factory()->create()->id,
            'type' => 'in',
            'quantity' => 1,
        ])->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/stock')->assertRedirect('/login');
    }

    public function test_variant_is_flagged_low_stock_at_or_below_threshold(): void
    {
        $variant = ProductVariant::factory()->create([
            'current_stock' => 5,
            'low_stock_threshold' => 10,
        ]);
        $this->assertTrue($variant->isLowStock());

        $variant->forceFill(['current_stock' => 10])->save();
        $this->assertTrue($variant->fresh()->isLowStock());

        $variant->forceFill(['current_stock' => 11])->save();
        $this->assertFalse($variant->fresh()->isLowStock());
    }

    public function test_variant_without_threshold_is_never_flagged_low_stock(): void
    {
        $variant = ProductVariant::factory()->create([
            'current_stock' => 0,
            'low_stock_threshold' => null,
        ]);

        $this->assertFalse($variant->isLowStock());
    }
}

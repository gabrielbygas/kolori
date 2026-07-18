<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PackagingType;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

// modify by claude
class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role, 'web');

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_admin_can_create_a_product_with_multiple_variants(): void
    {
        $admin = $this->userWithRole('admin');
        $category = ProductCategory::factory()->create();
        $unitPiece = Unit::factory()->create(['code' => 'pcs']);
        $unitLitre = Unit::factory()->create(['code' => 'l']);
        $bidon = PackagingType::factory()->create(['code' => 'bidon']);

        $response = $this->actingAs($admin)->post('/products', [
            'product_category_id' => $category->id,
            'name_fr' => 'Peinture email blanche',
            'name_en' => 'White enamel paint',
            'origin' => 'local',
            'description' => null,
            'is_active' => true,
            'variants' => [
                [
                    'unit_id' => $unitPiece->id,
                    'packaging_type_id' => null,
                    'sku' => 'PEB-PCS',
                    'retail_price' => 15,
                    'wholesale_price' => null,
                    'wholesale_min_qty' => null,
                    'is_active' => true,
                ],
                [
                    'unit_id' => $unitLitre->id,
                    'packaging_type_id' => $bidon->id,
                    'sku' => 'PEB-L-BIDON',
                    'retail_price' => 12,
                    'wholesale_price' => 10,
                    'wholesale_min_qty' => 5,
                    'is_active' => true,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/products');

        $product = Product::where('name_fr', 'Peinture email blanche')->firstOrFail();
        $this->assertSame(2, $product->variants()->count());
        $this->assertTrue($product->variants()->where('unit_id', $unitPiece->id)->exists());
        $this->assertTrue(
            $product->variants()->where('unit_id', $unitLitre->id)->where('packaging_type_id', $bidon->id)->exists()
        );
    }

    public function test_logisticien_can_create_a_product(): void
    {
        $logisticien = $this->userWithRole('logisticien');
        $category = ProductCategory::factory()->create();
        $unit = Unit::factory()->create();

        $response = $this->actingAs($logisticien)->post('/products', [
            'product_category_id' => $category->id,
            'name_fr' => 'Mastic',
            'name_en' => 'Putty',
            'origin' => 'local',
            'is_active' => true,
            'variants' => [
                ['unit_id' => $unit->id, 'retail_price' => 5],
            ],
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/products');
        $this->assertDatabaseHas('products', ['name_fr' => 'Mastic']);
    }

    public function test_vendeur_cannot_access_product_management(): void
    {
        $vendeur = $this->userWithRole('vendeur');

        $this->actingAs($vendeur)->get('/products')->assertForbidden();
        $this->actingAs($vendeur)->get('/products/create')->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/products')->assertRedirect('/login');
    }

    public function test_creating_a_product_requires_at_least_one_variant(): void
    {
        $admin = $this->userWithRole('admin');
        $category = ProductCategory::factory()->create();

        $response = $this->actingAs($admin)->post('/products', [
            'product_category_id' => $category->id,
            'name_fr' => 'Sans variante',
            'name_en' => 'No variant',
            'origin' => 'local',
            'is_active' => true,
            'variants' => [],
        ]);

        $response->assertSessionHasErrors('variants');
        $this->assertDatabaseMissing('products', ['name_fr' => 'Sans variante']);
    }

    public function test_duplicate_unit_and_packaging_combination_is_rejected_on_creation(): void
    {
        $admin = $this->userWithRole('admin');
        $category = ProductCategory::factory()->create();
        $unit = Unit::factory()->create();
        $packagingType = PackagingType::factory()->create();

        $response = $this->actingAs($admin)->post('/products', [
            'product_category_id' => $category->id,
            'name_fr' => 'Doublon variante',
            'name_en' => 'Duplicate variant',
            'origin' => 'local',
            'is_active' => true,
            'variants' => [
                ['unit_id' => $unit->id, 'packaging_type_id' => $packagingType->id, 'retail_price' => 10],
                ['unit_id' => $unit->id, 'packaging_type_id' => $packagingType->id, 'retail_price' => 12],
            ],
        ]);

        $response->assertSessionHasErrors('variants.1.unit_id');
        $this->assertDatabaseMissing('products', ['name_fr' => 'Doublon variante']);
    }

    public function test_database_rejects_duplicate_unit_and_packaging_combination_for_the_same_product(): void
    {
        $product = Product::factory()->create();
        $unit = Unit::factory()->create();
        $packagingType = PackagingType::factory()->create();

        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'packaging_type_id' => $packagingType->id,
        ]);

        $this->expectException(QueryException::class);

        DB::table('product_variants')->insert([
            'id' => (string) Str::uuid(),
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'packaging_type_id' => $packagingType->id,
            'retail_price' => 20,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_updating_a_product_syncs_variants(): void
    {
        $admin = $this->userWithRole('admin');
        $product = Product::factory()->create();
        $category = $product->category;

        $unitToKeep = Unit::factory()->create();
        $unitToRemove = Unit::factory()->create();
        $unitToAdd = Unit::factory()->create();

        $keptVariant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'unit_id' => $unitToKeep->id,
            'retail_price' => 10,
        ]);
        $removedVariant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'unit_id' => $unitToRemove->id,
            'retail_price' => 20,
        ]);

        $response = $this->actingAs($admin)->put("/products/{$product->id}", [
            'product_category_id' => $category->id,
            'name_fr' => $product->name_fr,
            'name_en' => $product->name_en,
            'origin' => $product->origin->value,
            'is_active' => true,
            'variants' => [
                [
                    'id' => $keptVariant->id,
                    'unit_id' => $unitToKeep->id,
                    'retail_price' => 11,
                ],
                [
                    'unit_id' => $unitToAdd->id,
                    'retail_price' => 30,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/products');

        $product->refresh();
        $this->assertSame(2, $product->variants()->count());
        $this->assertSame('11.00', $keptVariant->fresh()->retail_price);
        $this->assertTrue($product->variants()->where('unit_id', $unitToAdd->id)->exists());
        $this->assertModelMissing($removedVariant);
    }
}

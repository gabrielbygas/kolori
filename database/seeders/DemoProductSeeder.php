<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ProductOrigin;
use App\Models\PackagingType;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

// modify by claude
/**
 * Quelques produits d'exemple pour pouvoir tester le catalogue (liste/édition)
 * sans avoir à en créer manuellement. Réservé au dev local — voir DatabaseSeeder
 * (jamais exécuté automatiquement en production/déploiement client).
 */
class DemoProductSeeder extends Seeder
{
    public function run(): void
    {
        if (Product::query()->exists()) {
            return;
        }

        $unitsByCode = Unit::query()->get()->keyBy('code');
        $packagingByCode = PackagingType::query()->get()->keyBy('code');
        $categoriesBySlug = ProductCategory::query()->get()->keyBy('slug');

        $this->createProduct(
            categorySlug: 'peinture',
            categoriesBySlug: $categoriesBySlug,
            nameFr: 'Peinture email blanche',
            nameEn: 'White enamel paint',
            origin: ProductOrigin::Local,
            variants: [
                ['unit' => 'pcs', 'packaging' => null, 'retail_price' => 15, 'sku' => 'PEB-PCS'],
                ['unit' => 'l', 'packaging' => 'bidon', 'retail_price' => 12, 'wholesale_price' => 10, 'wholesale_min_qty' => 5, 'sku' => 'PEB-L-BIDON'],
            ],
            unitsByCode: $unitsByCode,
            packagingByCode: $packagingByCode,
        );

        $this->createProduct(
            categorySlug: 'chaux',
            categoriesBySlug: $categoriesBySlug,
            nameFr: 'Chaux hydratée',
            nameEn: 'Hydrated lime',
            origin: ProductOrigin::Local,
            variants: [
                ['unit' => 'kg', 'packaging' => 'sac', 'retail_price' => 0.80, 'wholesale_price' => 0.65, 'wholesale_min_qty' => 50, 'sku' => 'CHX-KG-SAC'],
            ],
            unitsByCode: $unitsByCode,
            packagingByCode: $packagingByCode,
        );

        $this->createProduct(
            categorySlug: 'pinceaux',
            categoriesBySlug: $categoriesBySlug,
            nameFr: 'Pinceau plat 2 pouces',
            nameEn: 'Flat brush 2 inches',
            origin: ProductOrigin::Imported,
            variants: [
                ['unit' => 'pcs', 'packaging' => null, 'retail_price' => 1.5, 'sku' => 'PIN-2P'],
            ],
            unitsByCode: $unitsByCode,
            packagingByCode: $packagingByCode,
        );

        $this->createProduct(
            categorySlug: 'colle',
            categoriesBySlug: $categoriesBySlug,
            nameFr: 'Colle AC23',
            nameEn: 'AC23 glue',
            origin: ProductOrigin::Imported,
            variants: [
                ['unit' => 'kg', 'packaging' => null, 'retail_price' => 3, 'wholesale_price' => 2.5, 'wholesale_min_qty' => 10, 'sku' => 'COL-AC23-KG'],
            ],
            unitsByCode: $unitsByCode,
            packagingByCode: $packagingByCode,
        );

        $this->createProduct(
            categorySlug: 'cable',
            categoriesBySlug: $categoriesBySlug,
            nameFr: 'Câble 35C',
            nameEn: '35C cable',
            origin: ProductOrigin::Imported,
            variants: [
                ['unit' => 'pcs', 'packaging' => 'rouleau', 'retail_price' => 25, 'sku' => 'CBL-35C-ROU'],
                ['unit' => 'pcs', 'packaging' => null, 'retail_price' => 2, 'sku' => 'CBL-35C-DET'],
            ],
            unitsByCode: $unitsByCode,
            packagingByCode: $packagingByCode,
        );
    }

    /**
     * @param  Collection<string, ProductCategory>  $categoriesBySlug
     * @param  array<int, array<string, mixed>>  $variants
     * @param  Collection<string, Unit>  $unitsByCode
     * @param  Collection<string, PackagingType>  $packagingByCode
     */
    private function createProduct(
        string $categorySlug,
        $categoriesBySlug,
        string $nameFr,
        string $nameEn,
        ProductOrigin $origin,
        array $variants,
        $unitsByCode,
        $packagingByCode,
    ): void {
        $product = Product::create([
            'product_category_id' => $categoriesBySlug[$categorySlug]->id,
            'name_fr' => $nameFr,
            'name_en' => $nameEn,
            'origin' => $origin,
            'is_active' => true,
        ]);

        foreach ($variants as $variant) {
            $product->variants()->create([
                'unit_id' => $unitsByCode[$variant['unit']]->id,
                'packaging_type_id' => $variant['packaging'] ? $packagingByCode[$variant['packaging']]->id : null,
                'sku' => $variant['sku'] ?? null,
                'retail_price' => $variant['retail_price'],
                'wholesale_price' => $variant['wholesale_price'] ?? null,
                'wholesale_min_qty' => $variant['wholesale_min_qty'] ?? null,
                'is_active' => true,
            ]);
        }
    }
}

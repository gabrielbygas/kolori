<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// modify by claude
class PackagingTypeSeeder extends Seeder
{
    /**
     * Emballages relevés dans les notes catalogue (§6 CLAUDE.md).
     */
    private const PACKAGING_TYPES = [
        ['code' => 'sac', 'name_fr' => 'Sac', 'name_en' => 'Bag'],
        ['code' => 'bidon', 'name_fr' => 'Bidon', 'name_en' => 'Jerrycan'],
        ['code' => 'boite', 'name_fr' => 'Boîte', 'name_en' => 'Box'],
        ['code' => 'carton', 'name_fr' => 'Carton', 'name_en' => 'Carton'],
        ['code' => 'rouleau', 'name_fr' => 'Rouleau', 'name_en' => 'Roll'],
        ['code' => 'paquet', 'name_fr' => 'Paquet', 'name_en' => 'Pack'],
    ];

    public function run(): void
    {
        foreach (self::PACKAGING_TYPES as $packagingType) {
            if (DB::table('packaging_types')->where('code', $packagingType['code'])->exists()) {
                continue;
            }

            DB::table('packaging_types')->insert([
                'id' => (string) Str::uuid(),
                'code' => $packagingType['code'],
                'name_fr' => $packagingType['name_fr'],
                'name_en' => $packagingType['name_en'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

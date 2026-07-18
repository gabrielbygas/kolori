<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// modify by claude
class UnitSeeder extends Seeder
{
    /**
     * Unités relevées dans les notes catalogue (§6 CLAUDE.md).
     */
    private const UNITS = [
        ['code' => 'kg', 'name_fr' => 'Kilogramme', 'name_en' => 'Kilogram'],
        ['code' => 'l', 'name_fr' => 'Litre', 'name_en' => 'Liter'],
        ['code' => 'pcs', 'name_fr' => 'Pièce', 'name_en' => 'Piece'],
    ];

    public function run(): void
    {
        foreach (self::UNITS as $unit) {
            if (DB::table('units')->where('code', $unit['code'])->exists()) {
                continue;
            }

            DB::table('units')->insert([
                'id' => (string) Str::uuid(),
                'code' => $unit['code'],
                'name_fr' => $unit['name_fr'],
                'name_en' => $unit['name_en'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

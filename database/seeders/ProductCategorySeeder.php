<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// modify by claude
class ProductCategorySeeder extends Seeder
{
    /**
     * Les 27 catégories de départ transcrites des notes client (§6 CLAUDE.md).
     * Le point 28 des notes manuscrites était vide/coupé — à compléter si le
     * client en précise le contenu (voir tasks.md, journal du sous-point 4.2).
     */
    private const CATEGORIES = [
        ['slug' => 'calcium', 'name_fr' => 'Calcium', 'name_en' => 'Calcium'],
        ['slug' => 'oxydes', 'name_fr' => 'Oxydes', 'name_en' => 'Oxide Pigments'],
        ['slug' => 'petrole', 'name_fr' => 'Pétrole', 'name_en' => 'Petroleum'],
        ['slug' => 'chaux', 'name_fr' => 'Chaux', 'name_en' => 'Lime'],
        ['slug' => 'titane', 'name_fr' => 'Titane', 'name_en' => 'Titanium Dioxide'],
        ['slug' => 'tylose', 'name_fr' => 'Tylose', 'name_en' => 'Tylose'],
        ['slug' => 'brosse', 'name_fr' => 'Brosse', 'name_en' => 'Brush'],
        ['slug' => 'brosse-chaux', 'name_fr' => 'Brosse à chaux', 'name_en' => 'Lime Brush'],
        ['slug' => 'ambro', 'name_fr' => 'Ambro', 'name_en' => 'Ambro'],
        ['slug' => 'palette', 'name_fr' => 'Palette', 'name_en' => 'Palette'],
        ['slug' => 'penta', 'name_fr' => 'Penta', 'name_en' => 'Penta'],
        ['slug' => 'cobalt', 'name_fr' => 'Cobalt', 'name_en' => 'Cobalt'],
        ['slug' => 'naphtol', 'name_fr' => 'Naphtol', 'name_en' => 'Naphthol'],
        ['slug' => 'ammoniac', 'name_fr' => 'Ammoniac', 'name_en' => 'Ammonia'],
        ['slug' => 'huile-de-peinture', 'name_fr' => 'Huile de peinture', 'name_en' => 'Paint Oil'],
        ['slug' => 'resine', 'name_fr' => 'Résine', 'name_en' => 'Resin'],
        ['slug' => 'tige-en-bois', 'name_fr' => 'Tige en bois', 'name_en' => 'Wooden Stick'],
        ['slug' => 'cable', 'name_fr' => 'Câble', 'name_en' => 'Cable'],
        ['slug' => 'rouleau', 'name_fr' => 'Rouleau', 'name_en' => 'Paint Roller'],
        ['slug' => 'roulette', 'name_fr' => 'Roulette', 'name_en' => 'Roller Wheel'],
        ['slug' => 'colle', 'name_fr' => 'Colle', 'name_en' => 'Glue'],
        ['slug' => 'seaux', 'name_fr' => 'Seaux', 'name_en' => 'Buckets'],
        ['slug' => 'collante', 'name_fr' => 'Bande collante', 'name_en' => 'Adhesive Tape'],
        ['slug' => 'peinture', 'name_fr' => 'Peinture', 'name_en' => 'Paint'],
        ['slug' => 'mastic', 'name_fr' => 'Mastic', 'name_en' => 'Putty'],
        ['slug' => 'paillette-or', 'name_fr' => "Paillette d'or", 'name_en' => 'Gold Glitter'],
        ['slug' => 'pinceaux', 'name_fr' => 'Pinceaux', 'name_en' => 'Paintbrushes'],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $category) {
            if (DB::table('product_categories')->where('slug', $category['slug'])->exists()) {
                continue;
            }

            DB::table('product_categories')->insert([
                'id' => (string) Str::uuid(),
                'slug' => $category['slug'],
                'name_fr' => $category['name_fr'],
                'name_en' => $category['name_en'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        // modify by claude
        $this->call(UnitSeeder::class);
        $this->call(PackagingTypeSeeder::class);
        $this->call(ProductCategorySeeder::class);

        $admin = User::factory()->create([
            'name' => 'Admin Kolori',
            'email' => 'admin@kolori.test',
        ]);
        $admin->assignRole('admin');

        // modify by claude
        // Produits d'exemple : dev local uniquement, jamais en production/déploiement client.
        if (app()->environment('local')) {
            $this->call(DemoProductSeeder::class);
        }
    }
}

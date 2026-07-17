<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Roles fixes du projet (plan V1, point 7) — pas de constructeur de
     * permissions en V1, la liste est volontairement figée ici (max 5).
     */
    private const ROLES = [
        'admin',
        'vendeur',
        'logisticien',
    ];

    public function run(): void
    {
        foreach (self::ROLES as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}

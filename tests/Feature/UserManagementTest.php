<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

// modify by claude
class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role, 'web');

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_admin_can_create_a_user_with_a_role(): void
    {
        $admin = $this->userWithRole('admin');
        Role::findOrCreate('vendeur', 'web');

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Jean Vendeur',
            'email' => 'jean.vendeur@kolori.test',
            'phone' => null,
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'vendeur',
            'is_active' => true,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/users');

        $created = User::where('email', 'jean.vendeur@kolori.test')->firstOrFail();
        $this->assertTrue($created->hasRole('vendeur'));

        // L'admin doit rester connecté en tant qu'admin, pas être basculé
        // sur le compte qu'il vient de créer.
        $this->assertAuthenticatedAs($admin);
    }

    public function test_vendeur_cannot_access_user_management(): void
    {
        $vendeur = $this->userWithRole('vendeur');

        $this->actingAs($vendeur)->get('/users')->assertForbidden();
        $this->actingAs($vendeur)->get('/users/create')->assertForbidden();
    }

    public function test_logisticien_cannot_access_user_management(): void
    {
        $logisticien = $this->userWithRole('logisticien');

        $this->actingAs($logisticien)->get('/users')->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/users')->assertRedirect('/login');
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $admin = $this->userWithRole('admin');
        Role::findOrCreate('vendeur', 'web');
        User::factory()->create(['email' => 'existing@kolori.test']);

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Doublon',
            'email' => 'existing@kolori.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'vendeur',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_user_list_shows_roles(): void
    {
        $admin = $this->userWithRole('admin');
        $this->userWithRole('logisticien');

        $response = $this->actingAs($admin)->get('/users');

        $response->assertOk();
    }
}

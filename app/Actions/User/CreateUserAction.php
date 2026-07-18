<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;

// modify by claude
class CreateUserAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'is_active' => $data['is_active'] ?? true,
            ]);

            $user->assignRole($data['role']);

            return $user;
        });
    }
}

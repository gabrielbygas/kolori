<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\User\CreateUserAction;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

// modify by claude
class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->with('roles')
            ->orderBy('name')
            ->paginate(20)
            ->through(fn (User $user) => new UserResource($user));

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Users/Create');
    }

    public function store(StoreUserRequest $request, CreateUserAction $createUser): RedirectResponse
    {
        $createUser($request->validated());

        return redirect()->route('users.index')->with('success', 'Utilisateur créé.');
    }
}

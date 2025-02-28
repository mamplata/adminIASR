<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\CreateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Users\SearchUsersRequest;
use App\Mail\PasswordReset;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(SearchUsersRequest $request, UserService $userService)
    {
        $search = $request->validated('search');

        $users = $userService->getUsersWithSearch($search);
        $users->appends(['search' => $search]);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function store(CreateUserRequest $request, UserService $userService)
    {
        // Create the new user with a temporary password
        $user = $userService->createUser($request->validated());

        // Redirect with a success message
        return redirect()->route('users.index')
            ->with('success', 'User added successfully! A password reset email has been sent.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\CreateUserRequest;
use App\Http\Requests\Users\SearchUsersRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

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
        // The validated() method will only return the data that passed validation
        $userService->createUser($request->validated());

        return redirect()->route('users.index')
            ->with('success', 'User added successfully!');
    }
}

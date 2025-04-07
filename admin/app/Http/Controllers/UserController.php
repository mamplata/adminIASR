<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\CreateUserRequest;
use App\Services\UserService;
use App\Http\Requests\Users\SearchUsersRequest;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the users with optional search functionality.
     *
     * @param SearchUsersRequest $request Validated request containing the search query.
     * @param UserService $userService Service to handle user-related operations.
     * @return \Inertia\Response Renders the users index page with search results.
     */

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

    /**
     * Create a new user.
     *
     * @param CreateUserRequest $request Validated request containing user data.
     * @param UserService $userService Service to handle user-related operations.
     * @return \Illuminate\Http\RedirectResponse Redirects to the users index page with a success message.
     */
    public function store(CreateUserRequest $request, UserService $userService)
    {
        // Create the new user with a temporary password
        $user = $userService->createUser($request->validated());

        // Redirect with a success message
        return redirect()->route('users.index')
            ->with('success', 'User added successfully! A password reset email has been sent.');
    }
}

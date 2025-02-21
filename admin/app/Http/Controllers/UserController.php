<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::orderBy('created_at', 'desc');

        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Paginate results and return only name & email
        $users = $query->latest()
            ->paginate(5)
            ->appends(['search' => $request->input('search')])
            ->through(fn($user) => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]);

        // $users = $query->latest()->paginate(5)->appends(['search' => $request->input('search')]);
        return Inertia::render('Users/Index', ['users' => $users, 'search' => $request->input('search')]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('users.index');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($request->all());
        return redirect()->route('users.index');
    }
}

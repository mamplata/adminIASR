<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    public function createUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    public function getUsersWithSearch(?string $search, int $perPage = 5): LengthAwarePaginator
    {
        $query = User::orderBy('created_at', 'desc');


        $query->where(function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%");
        });


        return $query->paginate($perPage)
            ->through(fn($user) => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::withoutEvents(function () {
            User::create([
                'name'     => 'Admin1',
                'email'    => 'user@gmail.com',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]);
        });

        // $names = ['John', 'Jane', 'May'];

        // User::withoutEvents(function () use ($names) {
        //     for ($i = 1; $i <= 20; $i++) {
        //         // Cycle through the names array
        //         $name = $names[($i - 1) % count($names)];
        //         User::create([
        //             'name'     => $name . ' ' . $i, // e.g., "John 1", "Jane 2", "May 3", etc.
        //             'email'    => strtolower($name) . $i . '@example.com', // e.g., "john1@example.com"
        //             'password' => Hash::make('password'),
        //             'role'     => 'admin',
        //         ]);
        //     }
        // });
    }
}

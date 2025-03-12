<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Semester;

class SemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Semester::withoutEvents(function () {
            Semester::create([
                'year'     => 2025,
                'semester' => '2nd',
            ]);
        });
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class UnauthorizedLogSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Create 4 sample unauthorized logs
        for ($i = 0; $i < 50; $i++) {
            // Randomly assign time_type as either "IN" or "OUT"
            $time_type = $faker->randomElement(['IN', 'OUT']);

            // Generate a random created_at datetime within the past month
            $createdAt = $faker->dateTimeBetween('-1 month', 'now');
            // Generate a random updated_at datetime between created_at and now
            $updatedAt = $faker->dateTimeBetween($createdAt, 'now');

            DB::table('unauthorized_logs')->insert([
                // 'id' is usually auto-incremented; no need to specify it manually
                'device_id'  => $faker->numberBetween(1, 100),
                'uid'        => $faker->uuid,
                'time_type'  => $time_type,
                'reason'     => $faker->sentence, // Provide a random failure reason
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);
        }
    }
}

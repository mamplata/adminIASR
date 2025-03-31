<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class EntryLogSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Create 50 sample entry logs
        for ($i = 0; $i < 50; $i++) {
            // Choose a random status
            $status = $faker->randomElement(['success', 'failure']);
            // For a failure record, provide a failure reason; otherwise, leave it null.
            $failure_reason = ($status === 'failure') ? $faker->sentence : null;
            // Randomly assign time_type as either "IN" or "OUT"
            $time_type = $faker->randomElement(['IN', 'OUT']);

            // Generate a random created_at datetime within the past month
            $createdAt = $faker->dateTimeBetween('-1 month', 'now');
            // Generate a random updated_at datetime between created_at and now
            $updatedAt = $faker->dateTimeBetween($createdAt, 'now');

            DB::table('entry_logs')->insert([
                'device_id'      => $faker->numberBetween(1, 100),
                'uid'            => $faker->uuid,
                'student_id'     => '190' . $faker->numberBetween(1000, 9999),
                'time_type'      => $time_type,
                'status'         => $status,
                'failure_reason' => $failure_reason,
                'created_at'     => $createdAt,
                'updated_at'     => $updatedAt,
            ]);
        }
    }
}

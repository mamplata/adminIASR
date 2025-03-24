<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class AnnouncementSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Fixed announcement sample
        DB::table('announcements')->insert([
            'departments' => 'GENERAL',
            'publisher' => 'dsadasdsa',
            'type' => 'text',
            'content' => json_encode([
                'title' => 'Important School Announcement: New Academic Year Updates',
                'body'  => "Dear Students, Parents, and Faculty,\n\nWe are excited to announce several new initiatives for the upcoming academic year at our school. Our curriculum has been updated to include innovative learning methods and technology-enhanced classes to better prepare our students for the future.\n\nAdditionally, we are expanding our extracurricular offerings, including sports, arts, and STEM clubs, ensuring that every student finds an area to excel. We appreciate your continued support and look forward to a year of growth, achievement, and community engagement."
            ]),
            'publication_date' => Carbon::parse('2025-03-23T16:00:00.000000Z'),
            'end_date' => '2025-03-24',
            'created_at' => Carbon::parse('2025-03-24T05:57:54.000000Z'),
            'updated_at' => Carbon::parse('2025-03-24T05:57:54.000000Z'),
        ]);

        // Generate additional announcements using Faker
        for ($i = 0; $i < 10; $i++) {
            // Randomly decide how many paragraphs (between 1 and 3) to include in the body
            $paragraphCount = $faker->numberBetween(1, 3);
            $bodyParagraphs = $faker->paragraphs($paragraphCount, true);
            $bodyText = "Dear Students, Parents, and Faculty,\n\n" . $bodyParagraphs;

            DB::table('announcements')->insert([
                'departments' => 'GENERAL',
                'publisher' => $faker->company,
                'type' => 'text',
                'content' => json_encode([
                    'title' => 'School Announcement: ' . $faker->sentence,
                    'body'  => $bodyText
                ]),
                'publication_date' => Carbon::parse($faker->dateTimeBetween('-1 years', 'now')->format('Y-m-d H:i:s')),
                'end_date' => $faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}

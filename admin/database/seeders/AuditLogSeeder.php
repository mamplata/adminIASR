<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuditLog;
use Carbon\Carbon;

class AuditLogSeeder extends Seeder
{
    public function run()
    {
        $logs = [
            [
                'admin_id' => 1,
                'admin_name' => 'Admin1',
                'action' => 'create',
                'type' => 'Device',
                'type_id' => 2,
                'details' => json_encode([
                    'name' => 'dsadasdsa',
                    'status' => 'inactive',
                    'short_code' => 'A02ZAF',
                    'deviceFingerprint' => 'b3cedfff-1c9b-4ad2-beae-dda21ad1f908',
                    'updated_at' => '2025-03-12T05:51:12.000000Z',
                    'created_at' => '2025-03-12T05:51:12.000000Z',
                    'id' => 2
                ]),
                'created_at' => Carbon::parse('2024-03-08 13:51:13'),
                'updated_at' => Carbon::parse('2025-03-08 13:51:13')
            ],
            [
                'admin_id' => 1,
                'admin_name' => 'Admin1',
                'action' => 'update',
                'type' => 'Device',
                'type_id' => 1,
                'details' => json_encode([
                    'status' => 'active → inactive at March 12, 2025, 1:59 pm'
                ]),
                'created_at' => Carbon::parse('2025-03-09 13:59:44'),
                'updated_at' => Carbon::parse('2025-03-09 13:59:44')
            ],
            [
                'admin_id' => null,
                'admin_name' => 'System',
                'action' => 'update',
                'type' => 'Device',
                'type_id' => 1,
                'details' => json_encode([
                    'status' => 'inactive → active at March 12, 2025, 1:59 pm'
                ]),
                'created_at' => Carbon::parse('2025-03-09 13:59:51'),
                'updated_at' => Carbon::parse('2025-03-09 13:59:51')
            ],
            [
                'admin_id' => 1,
                'admin_name' => 'Admin1',
                'action' => 'delete',
                'type' => 'Device',
                'type_id' => 2,
                'details' => json_encode([
                    'time' => 'Deleted at March 12, 2025, 2:19 pm'
                ]),
                'created_at' => Carbon::parse('2025-03-10 14:19:49'),
                'updated_at' => Carbon::parse('2025-03-10 14:19:49')
            ],
            [
                'admin_id' => 1,
                'admin_name' => 'Admin1',
                'action' => 'create',
                'type' => 'Announcement',
                'type_id' => 1,
                'details' => json_encode([
                    'departments' => 'CAS: BSPSY; COE: BSCPE, BSECE, BSIE',
                    'publisher' => 'dsdsadasdas',
                    'type' => 'image',
                    'publication_date' => '2025-03-11T16:00:00.000000Z',
                    'content' => [
                        'file_name' => 'Untitled.png',
                        'file_path' => '/storage/announcements/DTYECueGGg5NSTqkN9Nz2MDwEh2G364qpUQwmSRs.png',
                        'mime_type' => 'image/png',
                        'size' => 14688
                    ],
                    'updated_at' => '2025-03-12T06:20:17.000000Z',
                    'created_at' => '2025-03-12T06:20:17.000000Z',
                    'id' => 1
                ]),
                'created_at' => Carbon::parse('2025-03-11 14:20:17'),
                'updated_at' => Carbon::parse('2025-03-11 14:20:17')
            ],
            [
                'admin_id' => 1,
                'admin_name' => 'Admin1',
                'action' => 'update',
                'type' => 'Announcement',
                'type_id' => 1,
                'details' => json_encode([
                    'publication_date' => '2025-03-12 00:00:00 → 2025-03-28 00:00:00 at March 12, 2025, 3:19 pm'
                ]),
                'created_at' => Carbon::parse('2025-03-12 15:19:44'),
                'updated_at' => Carbon::parse('2025-03-12 15:19:44')
            ]
        ];

        foreach ($logs as $log) {
            AuditLog::create($log);
        }
    }
}

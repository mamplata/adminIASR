<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// STUDENTS API: Returns full student details
Route::get('students/{studentId}', function ($studentId) {
    // Dummy students data
    $students = [
        '1901234' => [
            'studentId'        => '1901234',
            'lName'            => 'Smith',
            'fName'            => 'John',
            'program'          => 'BSIT',
            'department'       => 'CCS',
            'yearLevel'        => '1',
            'image'            => 'https://placehold.co/400',
            'last_enrolled_at' => "1st 2024",
        ],
        '1904568' => [
            'studentId'        => '1904568',
            'lName'            => 'Brown',
            'fName'            => 'Emily',
            'program'          => 'BSCS',
            'department'       => 'CCS',
            'yearLevel'        => '3',
            'image'            => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '1901111' => [
            'studentId'        => '1901111',
            'lName'            => 'Taylor',
            'fName'            => 'Michael',
            'program'          => 'BSIT',
            'department'       => 'CCS',
            'yearLevel'        => '2',
            'image'            => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '1901112' => [
            'studentId'        => '1901112',
            'lName'            => 'Taylor',
            'fName'            => 'Michael',
            'program'          => 'BSIT',
            'department'       => 'CCS',
            'yearLevel'        => '2',
            'image'            => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '1901113' => [
            'studentId'        => '1901113',
            'lName'            => 'Bennett',
            'fName'            => 'Oliver',
            'program'          => 'BSIT',
            'department'       => 'CCS',
            'yearLevel'        => '4',
            'image'            => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2301697' => [
            'studentId'        => '2301697',
            'lName'            => 'Reynolds',
            'fName'            => 'Sophia',
            'program'          => 'BSCpE',
            'department'       => 'COE',
            'yearLevel'        => '3',
            'image'            => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2101996' => [
            'studentId'        => '2101996',
            'lName'            => 'Harrison',
            'fName'            => 'Liam',
            'program'          => 'BSBA',
            'department'       => 'CBAA',
            'yearLevel'        => '2',
            'image'            => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2001225' => [
            'studentId'        => '2001225',
            'lName'            => 'Carter',
            'fName'            => 'Isabella',
            'program'          => 'BSBA',
            'department'       => 'CBAA',
            'yearLevel'        => '1',
            'image'            => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2101355' => [
            'studentId'        => '2101355',
            'lName'            => 'Thompson',
            'fName'            => 'Noah',
            'program'          => 'BSPSY',
            'department'       => 'CAS',
            'yearLevel'        => '3',
            'image'            => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2301197' => [
            'studentId'        => '2301197',
            'lName'            => 'Mitchell',
            'fName'            => 'Ava',
            'program'          => 'BSIE',
            'department'       => 'COE',
            'yearLevel'        => '2',
            'image'            => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        // ... add additional student entries as needed (total 31 or more)
    ];

    if (array_key_exists($studentId, $students)) {
        return response()->json([
            'student' => $students[$studentId]
        ]);
    }

    return response()->json([
        'error' => 'Student not found'
    ], 404);
})->name('students.fetch');

// SCHEDULE API: Returns the pre-defined schedule from dummy data
Route::get('schedule/{studentId}', function ($studentId) {
    // Dummy schedule data for students

    $schedules = [
        '1901234' => [
            'schedule' => [
                [
                    'id'                => 1,
                    'courseCode'        => 'ITEW6',
                    'courseDescription' => 'Web Frameworks',
                    'day'               => 'Monday/Tuesday',
                    'time'              => '10:00 AM - 1:00 PM',
                    'room'              => 'Room 202',
                    'section'           => '4IT-A',
                ]
            ]
        ],
        '1904568' => [
            'schedule' => [
                [
                    'id'                => 2,
                    'courseCode'        => 'IT101',
                    'courseDescription' => 'Introduction to IT',
                    'day'               => 'Wednesday',
                    'time'              => '08:00 AM - 09:30 AM',
                    'room'              => 'Room 101',
                    'section'           => '4IT-B',
                ]
            ]
        ],
        '1901111' => [
            'schedule' => [
                [
                    'id'                => 3,
                    'courseCode'        => 'CS102',
                    'courseDescription' => 'Programming Basics',
                    'day'               => 'Thursday',
                    'time'              => '10:00 AM - 11:30 AM',
                    'room'              => 'Room 303',
                    'section'           => '4IT-C',
                ]
            ]
        ],
        '1901112' => [
            'schedule' => [
                [
                    'id'                => 4,
                    'courseCode'        => 'CS301',
                    'courseDescription' => 'Data Structures',
                    'day'               => 'Friday',
                    'time'              => '01:00 PM - 02:30 PM',
                    'room'              => 'Lab 101',
                    'section'           => '4IT-D',
                ]
            ]
        ],
        '1901113' => [
            'schedule' => [
                [
                    'id'                => 5,
                    'courseCode'        => 'IT202',
                    'courseDescription' => 'Networking Fundamentals',
                    'day'               => 'Monday/Tuesday',
                    'time'              => '02:00 PM - 03:30 PM',
                    'room'              => 'Room 202',
                    'section'           => '4IT-E',
                ]
            ]
        ],
        '2301697' => [
            'schedule' => [
                [
                    'id'                => 6,
                    'courseCode'        => 'CS401',
                    'courseDescription' => 'Software Engineering',
                    'day'               => 'Tuesday/Thursday',
                    'time'              => '10:00 AM - 1:00 PM',
                    'room'              => 'Room 303',
                    'section'           => '4IT-F',
                ],
                // Additional schedule entry for this student
                [
                    'id'                => 7,
                    'courseCode'        => 'IT305',
                    'courseDescription' => 'Database Management',
                    'day'               => 'Monday',
                    'time'              => '02:00 PM - 03:30 PM',
                    'room'              => 'Room 105',
                    'section'           => '4IT-F2',
                ]
            ]
        ],
        '2101996' => [
            'schedule' => [
                [
                    'id'                => 8,
                    'courseCode'        => 'IT305',
                    'courseDescription' => 'Database Management',
                    'day'               => 'Wednesday',
                    'time'              => '01:00 PM - 02:30 PM',
                    'room'              => 'Room 101',
                    'section'           => '4IT-G',
                ]
            ]
        ],
        '2001225' => [
            'schedule' => [
                [
                    'id'                => 9,
                    'courseCode'        => 'IT101',
                    'courseDescription' => 'Introduction to IT',
                    'day'               => 'Thursday',
                    'time'              => '08:00 AM - 09:30 AM',
                    'room'              => 'Room 202',
                    'section'           => '4IT-H',
                ]
            ]
        ],
        '2101355' => [
            'schedule' => [
                [
                    'id'                => 10,
                    'courseCode'        => 'CS102',
                    'courseDescription' => 'Programming Basics',
                    'day'               => 'Friday',
                    'time'              => '10:00 AM - 11:30 AM',
                    'room'              => 'Lab 101',
                    'section'           => '4IT-I',
                ]
            ]
        ],
        '2301197' => [
            'schedule' => [
                [
                    'id'                => 11,
                    'courseCode'        => 'CS301',
                    'courseDescription' => 'Data Structures',
                    'day'               => 'Monday/Tuesday',
                    'time'              => '01:00 PM - 02:30 PM',
                    'room'              => 'Room 303',
                    'section'           => '4IT-J',
                ]
            ]
        ],
        '2301888' => [
            'schedule' => [
                [
                    'id'                => 12,
                    'courseCode'        => 'IT202',
                    'courseDescription' => 'Networking Fundamentals',
                    'day'               => 'Wednesday',
                    'time'              => '02:00 PM - 03:30 PM',
                    'room'              => 'Room 101',
                    'section'           => '4IT-K',
                ]
            ]
        ],
        '2301932' => [
            'schedule' => [
                [
                    'id'                => 13,
                    'courseCode'        => 'CS401',
                    'courseDescription' => 'Software Engineering',
                    'day'               => 'Tuesday/Thursday',
                    'time'              => '10:00 AM - 1:00 PM',
                    'room'              => 'Room 202',
                    'section'           => '4IT-L',
                ]
            ]
        ],
        '2301887' => [
            'schedule' => [
                [
                    'id'                => 14,
                    'courseCode'        => 'IT305',
                    'courseDescription' => 'Database Management',
                    'day'               => 'Friday',
                    'time'              => '08:00 AM - 09:30 AM',
                    'room'              => 'Room 303',
                    'section'           => '4IT-M',
                ]
            ]
        ],
        '2301999' => [
            'schedule' => [
                [
                    'id'                => 15,
                    'courseCode'        => 'ITEW6',
                    'courseDescription' => 'Web Frameworks',
                    'day'               => 'Monday/Tuesday',
                    'time'              => '10:00 AM - 1:00 PM',
                    'room'              => 'Room 202',
                    'section'           => '4IT-N',
                ]
            ]
        ],
        '2300790' => [
            'schedule' => [
                [
                    'id'                => 16,
                    'courseCode'        => 'IT101',
                    'courseDescription' => 'Introduction to IT',
                    'day'               => 'Wednesday',
                    'time'              => '10:00 AM - 11:30 AM',
                    'room'              => 'Room 101',
                    'section'           => '4IT-O',
                ]
            ]
        ],
        '2301638' => [
            'schedule' => [
                [
                    'id'                => 17,
                    'courseCode'        => 'CS102',
                    'courseDescription' => 'Programming Basics',
                    'day'               => 'Thursday',
                    'time'              => '01:00 PM - 02:30 PM',
                    'room'              => 'Lab 101',
                    'section'           => '4IT-P',
                ]
            ]
        ],
        '2301941' => [
            'schedule' => [
                [
                    'id'                => 18,
                    'courseCode'        => 'CS301',
                    'courseDescription' => 'Data Structures',
                    'day'               => 'Friday',
                    'time'              => '02:00 PM - 03:30 PM',
                    'room'              => 'Room 202',
                    'section'           => '4IT-Q',
                ]
            ]
        ],
        '2302020' => [
            'schedule' => [
                [
                    'id'                => 19,
                    'courseCode'        => 'IT202',
                    'courseDescription' => 'Networking Fundamentals',
                    'day'               => 'Monday/Tuesday',
                    'time'              => '08:00 AM - 09:30 AM',
                    'room'              => 'Room 303',
                    'section'           => '4IT-R',
                ]
            ]
        ],
        '2201384' => [
            'schedule' => [
                [
                    'id'                => 20,
                    'courseCode'        => 'CS401',
                    'courseDescription' => 'Software Engineering',
                    'day'               => 'Wednesday',
                    'time'              => '10:00 AM - 1:00 PM',
                    'room'              => 'Room 101',
                    'section'           => '4IT-S',
                ]
            ]
        ],
        '2101907' => [
            'schedule' => [
                [
                    'id'                => 21,
                    'courseCode'        => 'IT305',
                    'courseDescription' => 'Database Management',
                    'day'               => 'Thursday',
                    'time'              => '01:00 PM - 02:30 PM',
                    'room'              => 'Lab 101',
                    'section'           => '4IT-T',
                ]
            ]
        ],
        '2102183' => [
            'schedule' => [
                [
                    'id'                => 22,
                    'courseCode'        => 'ITEW6',
                    'courseDescription' => 'Web Frameworks',
                    'day'               => 'Friday',
                    'time'              => '10:00 AM - 1:00 PM',
                    'room'              => 'Room 202',
                    'section'           => '4IT-U',
                ]
            ]
        ],
        '1901114' => [
            'schedule' => [
                [
                    'id'                => 23,
                    'courseCode'        => 'IT101',
                    'courseDescription' => 'Introduction to IT',
                    'day'               => 'Monday/Tuesday',
                    'time'              => '08:00 AM - 09:30 AM',
                    'room'              => 'Room 303',
                    'section'           => '4IT-V',
                ]
            ]
        ],
        '2404321' => [
            'schedule' => [
                [
                    'id'                => 24,
                    'courseCode'        => 'CS102',
                    'courseDescription' => 'Programming Basics',
                    'day'               => 'Wednesday',
                    'time'              => '10:00 AM - 11:30 AM',
                    'room'              => 'Lab 101',
                    'section'           => '4IT-W',
                ]
            ]
        ],
        '2202025' => [
            'schedule' => [
                [
                    'id'                => 25,
                    'courseCode'        => 'CS301',
                    'courseDescription' => 'Data Structures',
                    'day'               => 'Thursday',
                    'time'              => '01:00 PM - 02:30 PM',
                    'room'              => 'Room 202',
                    'section'           => '4IT-X',
                ]
            ]
        ],
        '1900624' => [
            'schedule' => [
                [
                    'id'                => 26,
                    'courseCode'        => 'IT202',
                    'courseDescription' => 'Networking Fundamentals',
                    'day'               => 'Friday',
                    'time'              => '02:00 PM - 03:30 PM',
                    'room'              => 'Room 101',
                    'section'           => '4IT-Y',
                ]
            ]
        ],
        '2302207' => [
            'schedule' => [
                [
                    'id'                => 27,
                    'courseCode'        => 'CS401',
                    'courseDescription' => 'Software Engineering',
                    'day'               => 'Monday/Tuesday',
                    'time'              => '10:00 AM - 1:00 PM',
                    'room'              => 'Room 303',
                    'section'           => '4IT-Z',
                ]
            ]
        ],
        '2302041' => [
            'schedule' => [
                [
                    'id'                => 28,
                    'courseCode'        => 'IT305',
                    'courseDescription' => 'Database Management',
                    'day'               => 'Wednesday',
                    'time'              => '08:00 AM - 09:30 AM',
                    'room'              => 'Room 202',
                    'section'           => '4IT-AA',
                ]
            ]
        ],
        '2302494' => [
            'schedule' => [
                [
                    'id'                => 29,
                    'courseCode'        => 'ITEW6',
                    'courseDescription' => 'Web Frameworks',
                    'day'               => 'Thursday',
                    'time'              => '10:00 AM - 1:00 PM',
                    'room'              => 'Room 101',
                    'section'           => '4IT-AB',
                ]
            ]
        ],
        '2301990' => [
            'schedule' => [
                [
                    'id'                => 30,
                    'courseCode'        => 'IT101',
                    'courseDescription' => 'Introduction to IT',
                    'day'               => 'Friday',
                    'time'              => '01:00 PM - 02:30 PM',
                    'room'              => 'Lab 101',
                    'section'           => '4IT-AC',
                ]
            ]
        ],
        '2302017' => [
            'schedule' => [
                [
                    'id'                => 31,
                    'courseCode'        => 'CS102',
                    'courseDescription' => 'Programming Basics',
                    'day'               => 'Monday/Tuesday',
                    'time'              => '08:00 AM - 09:30 AM',
                    'room'              => 'Room 202',
                    'section'           => '4IT-AD',
                ]
            ]
        ],
        '2102400' => [
            'schedule' => [
                [
                    'id'                => 32,
                    'courseCode'        => 'CS301',
                    'courseDescription' => 'Data Structures',
                    'day'               => 'Wednesday',
                    'time'              => '10:00 AM - 11:30 AM',
                    'room'              => 'Room 303',
                    'section'           => '4IT-AE',
                ]
            ]
        ],
        '1901116' => [
            'schedule' => [
                [
                    'id'                => 33,
                    'courseCode'        => 'CS301',
                    'courseDescription' => 'Data Structures',
                    'day'               => 'Wednesday',
                    'time'              => '10:00 AM - 11:30 AM',
                    'room'              => 'Room 303',
                    'section'           => '4IT-A',
                ],
                [
                    'id'                => 31,
                    'courseCode'        => 'CS102',
                    'courseDescription' => 'Programming Basics',
                    'day'               => 'Wednesday/Friday',
                    'time'              => '08:00 AM - 09:30 AM',
                    'room'              => 'Room 202',
                    'section'           => '4IT-AD',
                ]
            ]
        ],
    ];

    if (!array_key_exists($studentId, $schedules)) {
        return response()->json([
            'studentId' => $studentId,
            'schedule'  => [],
            'message'   => 'No schedule for today'
        ]);
    }

    // Simply return the pre-defined schedule for the student.
    return response()->json($schedules[$studentId]);
})->name('schedule.fetch');

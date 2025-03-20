<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('students/{studentId}', function ($studentId) {
    // Define the three students
    $students = [
        '1901234' => [
            'studentId'  => '1901234',
            'lName'      => 'Smith',
            'fName'      => 'John',
            'program'    => 'BSIT',
            'department' => 'CCS',
            'yearLevel'  => '1',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "1st 2024",
        ],
        '1904568' => [
            'studentId'  => '1904568',
            'lName'      => 'Brown',
            'fName'      => 'Emily',
            'program'    => 'BSCS',
            'department' => 'CCS',
            'yearLevel'  => '3',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '1901111' => [
            'studentId'  => '1901111',
            'lName'      => 'Taylor',
            'fName'      => 'Michael',
            'program'    => 'BSIT',
            'department' => 'CCS',
            'yearLevel'  => '2',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '1901112' => [
            'studentId'  => '1901112',
            'lName'      => 'Taylor',
            'fName'      => 'Michael',
            'program'    => 'BSIT',
            'department' => 'CCS',
            'yearLevel'  => '2',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '1901113' => [
            'studentId'  => '1901113',
            'lName'      => 'Bennett',
            'fName'      => 'Oliver',
            'program'    => 'BSIT',
            'department' => 'CCS',
            'yearLevel'  => '4',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2301697' => [
            'studentId'  => '2301697',
            'lName'      => 'Reynolds',
            'fName'      => 'Sophia',
            'program'    => 'BSCpE',
            'department' => 'COE',
            'yearLevel'  => '3',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2101996' => [
            'studentId'  => '2101996',
            'lName'      => 'Harrison',
            'fName'      => 'Liam',
            'program'    => 'BSBA',
            'department' => 'CBAA',
            'yearLevel'  => '2',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2001225' => [
            'studentId'  => '2001225',
            'lName'      => 'Carter',
            'fName'      => 'Isabella',
            'program'    => 'BSBA',
            'department' => 'CBAA',
            'yearLevel'  => '1',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2101355' => [
            'studentId'  => '2101355',
            'lName'      => 'Thompson',
            'fName'      => 'Noah',
            'program'    => 'BSPSY',
            'department' => 'CAS',
            'yearLevel'  => '3',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2301197' => [
            'studentId'  => '2301197',
            'lName'      => 'Mitchell',
            'fName'      => 'Ava',
            'program'    => 'BSIE',
            'department' => 'COE',
            'yearLevel'  => '2',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2301888' => [
            'studentId'  => '2301888',
            'lName'      => 'Sullivan',
            'fName'      => 'Elijah',
            'program'    => 'BSA',
            'department' => 'CBAA',
            'yearLevel'  => '2',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2301932' => [
            'studentId'  => '2301932',
            'lName'      => 'Richardson',
            'fName'      => 'Mia',
            'program'    => 'BSA',
            'department' => 'CBAA',
            'yearLevel'  => '3',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2301887' => [
            'studentId'  => '2301887',
            'lName'      => 'Walker',
            'fName'      => 'James',
            'program'    => 'BSA',
            'department' => 'CBAA',
            'yearLevel'  => '3',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2301999' => [
            'studentId'  => '2301999',
            'lName'      => 'Hayes',
            'fName'      => 'Charlotte',
            'program'    => 'BSEDM',
            'department' => 'COED',
            'yearLevel'  => '2',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2300790' => [
            'studentId'  => '2300790',
            'lName'      => 'Cooper',
            'fName'      => 'Benjamin',
            'program'    => 'BSA',
            'department' => 'CBAA',
            'yearLevel'  => '2',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2301638' => [
            'studentId'  => '2301638',
            'lName'      => 'Foster',
            'fName'      => 'Amelia',
            'program'    => 'BSEDE',
            'department' => 'COED',
            'yearLevel'  => '3',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2301941' => [
            'studentId'  => '2301941',
            'lName'      => 'Stevenson',
            'fName'      => 'Henry',
            'program'    => 'BSBA',
            'department' => 'CBAA',
            'yearLevel'  => '1',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2302020' => [
            'studentId'  => '2302020',
            'lName'      => 'Collins',
            'fName'      => 'Harper',
            'program'    => 'BSBA',
            'department' => 'CBAA',
            'yearLevel'  => '4',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2201384' => [
            'studentId'  => '2201384',
            'lName'      => 'Bryant',
            'fName'      => 'Lucas',
            'program'    => 'BSPSY',
            'department' => 'CAS',
            'yearLevel'  => '4',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2101907' => [
            'studentId'  => '2101907',
            'lName'      => 'Parker',
            'fName'      => 'Evelyn',
            'program'    => 'BSIT',
            'department' => 'CCS',
            'yearLevel'  => '3',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2102183' => [
            'studentId'  => '2102183',
            'lName'      => 'Turner',
            'fName'      => 'Alexander',
            'program'    => 'BSIT',
            'department' => 'CCS',
            'yearLevel'  => '3',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '1901114' => [
            'studentId'  => '1901114',
            'lName'      => 'Brooks',
            'fName'      => 'Abigail',
            'program'    => 'BSIT',
            'department' => 'CCS',
            'yearLevel'  => '3',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2404321' => [
            'studentId'  => '2404321',
            'lName'      => 'Morgan',
            'fName'      => 'Daniel',
            'program'    => 'BSEDF',
            'department' => 'COED',
            'yearLevel'  => '1',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2202025' => [
            'studentId'  => '2202025',
            'lName'      => 'Fisher',
            'fName'      => 'Scarlett',
            'program'    => 'BSN',
            'department' => 'CHAS',
            'yearLevel'  => '3',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '1900624' => [
            'studentId'  => '1900624',
            'lName'      => 'Coleman',
            'fName'      => 'Matthew',
            'program'    => 'BSA',
            'department' => 'CBAA',
            'yearLevel'  => '4',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2302207' => [
            'studentId'  => '2302207',
            'lName'      => 'Chapman',
            'fName'      => 'Grace',
            'program'    => 'BSCS',
            'department' => 'CCS',
            'yearLevel'  => '2',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2302041' => [
            'studentId'  => '2302041',
            'lName'      => 'Lawson',
            'fName'      => 'David',
            'program'    => 'BSA',
            'department' => 'CBAA',
            'yearLevel'  => '3',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2302494' => [
            'studentId'  => '2302494',
            'lName'      => 'Anderson',
            'fName'      => 'Lily',
            'program'    => 'BSCS',
            'department' => 'CCS',
            'yearLevel'  => '2',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2301990' => [
            'studentId'  => '2301990',
            'lName'      => 'Dawson',
            'fName'      => 'Samuel',
            'program'    => 'BSBA',
            'department' => 'CBAA',
            'yearLevel'  => '1',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2302017' => [
            'studentId'  => '2302017',
            'lName'      => 'Greene',
            'fName'      => 'Aurora',
            'program'    => 'BSBA',
            'department' => 'CBAA',
            'yearLevel'  => '1',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2102400' => [
            'studentId'  => '2102400',
            'lName'      => 'Baldwin',
            'fName'      => 'Christopher',
            'program'    => 'BSCS',
            'department' => 'CCS',
            'yearLevel'  => '3',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '1901115' => [
            'studentId'  => '1901115',
            'lName'      => 'Russell',
            'fName'      => 'Hannah',
            'program'    => 'BSIT',
            'department' => 'CCS',
            'yearLevel'  => '4',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '2300995' => [
            'studentId'  => '2300995',
            'lName'      => 'Palmer',
            'fName'      => 'Nathan',
            'program'    => 'BSBA',
            'department' => 'CBAA',
            'yearLevel'  => '3',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '1901116' => [
            'studentId'  => '1901116',
            'lName'      => 'Simmons',
            'fName'      => 'Stella',
            'program'    => 'BSIT',
            'department' => 'CCS',
            'yearLevel'  => '4',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
    ];

    // Check if the requested studentId exists
    if (array_key_exists($studentId, $students)) {
        return response()->json([
            'student' => $students[$studentId]
        ]);
    }

    // If studentId is not found, return an error response
    return response()->json([
        'error' => 'Student not found'
    ], 404);
})->name('students.fetch');
Route::get('schedule/{studentId}', function ($studentId) {
    // List of valid student IDs
    $validStudents = ['1901234', '1904568', '1901111', '1901112'];

    // Check if the student exists
    if (!in_array($studentId, $validStudents)) {
        return response()->json([
            'studentId' => $studentId,
            'schedule'  => [],
            'message'   => 'No schedule for today'
        ]);
    }

    // Define available courses (no filtering by program)
    $courses = [
        ['courseCode' => 'IT101', 'courseDescription' => 'Introduction to IT'],
        ['courseCode' => 'CS102', 'courseDescription' => 'Programming Basics'],
        ['courseCode' => 'CS301', 'courseDescription' => 'Data Structures'],
        ['courseCode' => 'IT202', 'courseDescription' => 'Networking Fundamentals'],
        ['courseCode' => 'CS401', 'courseDescription' => 'Software Engineering'],
        ['courseCode' => 'IT305', 'courseDescription' => 'Database Management'],
    ];

    // Determine the schedule day:
    if ($studentId === '1901111') {
        $day = 'Wednesday';
    } elseif ($studentId === '1904568') {
        $day = 'Thursday';
    } else {
        // Assign schedule based on today's day
        $today = date('l'); // e.g., Monday, Tuesday, etc.
        if (in_array($today, ['Monday', 'Wednesday', 'Friday'])) {
            $day = 'MWF';
        } elseif (in_array($today, ['Tuesday', 'Thursday'])) {
            $day = 'TTh';
        } elseif ($today === 'Saturday') {
            $day = 'Sat';
        } else {
            $day = null;
        }
    }

    // No schedule case
    if (!$day) {
        return response()->json([
            'studentId' => $studentId,
            'schedule'  => [],
            'message'   => 'No schedule for today'
        ]);
    }

    // Define additional schedule details
    $times    = ['08:00 AM - 09:30 AM', '10:00 AM - 11:30 AM', '01:00 PM - 02:30 PM', '02:00 PM - 03:30 PM'];
    $rooms    = ['Room 101', 'Room 202', 'Room 303', 'Lab 101'];
    $sections = ['A1', 'B1', 'C1', 'D1'];

    // Generate a random schedule (between 2 and 5 courses)
    $schedule = [];
    for ($i = 1; $i <= rand(2, 5); $i++) {
        $course = $courses[array_rand($courses)];
        $schedule[] = [
            'id'                => $i,
            'courseCode'        => $course['courseCode'],
            'courseDescription' => $course['courseDescription'],
            'day'               => $day,
            'time'              => $times[array_rand($times)],
            'room'              => $rooms[array_rand($rooms)],
            'section'           => $sections[array_rand($sections)],
        ];
    }

    return response()->json([
        'studentId' => $studentId,
        'schedule'  => $schedule,
    ]);
})->name('schedule.fetch');

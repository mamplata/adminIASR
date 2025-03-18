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
    // Generate a dynamic schedule with random courses
    $courses = [
        ['courseCode' => 'IT101', 'courseDescription' => 'Introduction to IT'],
        ['courseCode' => 'CS102', 'courseDescription' => 'Programming Basics'],
        ['courseCode' => 'CS301', 'courseDescription' => 'Data Structures'],
        ['courseCode' => 'IT202', 'courseDescription' => 'Networking Fundamentals'],
        ['courseCode' => 'CS401', 'courseDescription' => 'Software Engineering'],
        ['courseCode' => 'IT305', 'courseDescription' => 'Database Management'],
    ];

    $days = ['MWF', 'TTh', 'Sat'];
    $times = ['08:00 AM - 09:30 AM', '10:00 AM - 11:30 AM', '01:00 PM - 02:30 PM', '02:00 PM - 03:30 PM'];
    $rooms = ['Room 101', 'Room 202', 'Room 303', 'Lab 101'];
    $sections = ['A1', 'B1', 'C1', 'D1'];

    // Generate a random schedule for the given student
    $schedule = [];
    for ($i = 1; $i <= rand(2, 5); $i++) {
        $course = $courses[array_rand($courses)];
        $schedule[] = [
            'id'                => $i,
            'courseCode'        => $course['courseCode'],
            'courseDescription' => $course['courseDescription'],
            'day'               => $days[array_rand($days)],
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

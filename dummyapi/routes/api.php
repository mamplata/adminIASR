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

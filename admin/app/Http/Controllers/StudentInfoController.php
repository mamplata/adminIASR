<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentInfos\StoreStudentInfoRequest;
use App\Models\StudentInfo;
use App\Services\StudentInfoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StudentInfoController extends Controller
{
    protected $studentInfoService;

    public function __construct(StudentInfoService $studentInfoService)
    {
        $this->studentInfoService = $studentInfoService;
    }

    public function check(Request $request)
    {
        $request->validate([
            'studentId' => 'required|integer',
        ]);

        return response()->json($this->studentInfoService->checkStudentID($request->studentId));
    }

    public function store(StoreStudentInfoRequest $request)
    {
        // Extract the last_enrolled_at value (e.g., "2nd 2024-2025")
        $lastEnrolledAt = $request->last_enrolled_at;

        // Extract semester (e.g., "2nd") and year (e.g., "2024")
        preg_match('/(\d+)(?:st|nd|rd|th)\s(\d{4})/', $lastEnrolledAt, $matches);

        if (!$matches) {
            return response()->json([
                'error' => 'Invalid last_enrolled_at format.'
            ], 422);
        }



        $enrolledSemester = $matches[1] . 'nd'; // Extract semester (ensure format like '2nd')
        $enrolledYear = $matches[2]; // Extract year

        // Check if the student is enrolled in the current semester
        $currentSemester = DB::table('semesters')
            ->where('year', $enrolledYear)
            ->where('semester', $enrolledSemester)
            ->first();


        if (!$currentSemester) {
            return response()->json([
                'error' => 'Student is not currently enrolled and cannot be registered.'
            ], 422);
        }

        $this->studentInfoService->storeStudentInfo($request->validated());

        return redirect()->route('registered-cards.index')
            ->with('success', 'Student Info created!');
    }

    public function indexApi()
    {
        $users = StudentInfo::all();
        return response()->json($users);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentInfos\StoreStudentInfoRequest;
use App\Models\StudentInfo;
use App\Services\StudentInfoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class StudentInfoController extends Controller
{
    protected $studentInfoService;

    /**
     * Construct a new StudentInfoController instance.
     *
     * @param  \App\Services\StudentInfoService  $studentInfoService
     * @return void
     */
    public function __construct(StudentInfoService $studentInfoService)
    {
        $this->studentInfoService = $studentInfoService;
    }

    /**
     * Check if a student ID exists in the student infos table, and return additional information:
     * - student: the student information associated with the student ID (or null if not found)
     * - isEnrolled: whether the student is enrolled in the current semester
     * - message: a descriptive message about the result (or an error message if external API fails)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function check(Request $request)
    {
        $request->validate([
            'studentId' => 'required|integer',
        ]);

        return response()->json($this->studentInfoService->checkStudentID($request->studentId));
    }

    /**
     * Store a new student info entry in the database.
     *
     * Validates the input using the StoreStudentInfoRequest class, then:
     * - Extracts the last_enrolled_at value (e.g., "2nd 2025")
     * - Checks the format of last_enrolled_at using a regular expression
     * - Checks if the student is enrolled in the current semester
     * - Stores the student info using the validated data
     * - Returns a success response using Inertia
     *
     * @param  \App\Http\Requests\StudentInfos\StoreStudentInfoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreStudentInfoRequest $request)
    {

        // Extract the last_enrolled_at value (e.g., "2nd 2025")
        $lastEnrolledAt = $request->last_enrolled_at;

        // Update the regular expression to match "2nd 2025" format
        preg_match('/(1st|2nd)\s(\d{4})/', $lastEnrolledAt, $matches);

        if (!$matches) {
            return back()
                ->withErrors(['error' => 'Invalid last_enrolled_at format.']);
        }

        $enrolledSemester = $matches[1]; // Extract semester (e.g., "2nd")
        $enrolledYear = $matches[2]; // Extract year (e.g., "2025")

        // Check if the student is enrolled in the current semester
        $currentSemester = DB::table('semesters')
            ->where('year', $enrolledYear)
            ->where('semester', $enrolledSemester)
            ->first();

        if (!$currentSemester) {
            return back()
                ->withErrors(['error' => 'Student is not currently enrolled.']);
        }

        // Store the student info using the validated data
        $this->studentInfoService->storeStudentInfo($request->validated());

        // Return success response using Inertia
        return redirect()->route('registered-cards.index')
            ->with('success', 'Student Info created!');
    }
}

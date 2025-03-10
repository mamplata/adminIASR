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
        // Extract the last_enrolled_at value (e.g., "2nd 2025")
        $lastEnrolledAt = $request->last_enrolled_at;

        // Update the regular expression to match "2nd 2025" format
        preg_match('/(1st|2nd)\s(\d{4})/', $lastEnrolledAt, $matches);

        if (!$matches) {
            return back()
                ->withErrors(['last_enrolled_at' => 'Invalid last_enrolled_at format.'])
                ->withInput(); // Keep the input data
        }

        $enrolledSemester = $matches[1]; // Extract semester (e.g., "2nd")
        $enrolledYear = $matches[2]; // Extract year (e.g., "2025")

        // Check if the student is enrolled in the current semester
        $currentSemester = DB::table('semesters')
            ->where('year', $enrolledYear)
            ->where('semester', $enrolledSemester)
            ->first();


        if (!$currentSemester) {
            return [
                'message' => 'Student is not currently enrolled and cannot be registered.',
            ];
        }

        // Store the student info using the validated data
        $this->studentInfoService->storeStudentInfo($request->validated());

        // Inertia response for success
        return redirect()->route('registered-cards.index')
            ->with('success', 'Student Info created!');
    }

    public function indexApi()
    {
        $users = StudentInfo::all();
        return response()->json($users);
    }
}

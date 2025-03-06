<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentInfos\StoreStudentInfoRequest;
use App\Models\StudentInfo;
use App\Services\StudentInfoService;
use Illuminate\Http\Request;
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
        // Check if the student is enrolled
        if (!$request->enrolled) {
            return response()->json([
                'error' => 'Student is not enrolled and cannot be registered.'
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

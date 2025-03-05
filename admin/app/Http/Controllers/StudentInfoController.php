<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentInfos\StoreStudentInfoRequest;
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
        $this->studentInfoService->storeStudentInfo($request->validated());

        return redirect()->route('registered-cards.index')
            ->with('success', 'Student Info created!');
    }
}

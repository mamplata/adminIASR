<?php

namespace App\Http\Controllers;

use App\Models\StudentInfo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentInfoController extends Controller
{
    public function index(Request $request)
    {
        $query = StudentInfo::orderBy('created_at', 'desc');

        // Optional search filtering across multiple columns
        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('studentId', 'LIKE', "%{$search}%")
                    ->orWhere('fName', 'LIKE', "%{$search}%")
                    ->orWhere('lName', 'LIKE', "%{$search}%")
                    ->orWhere('program', 'LIKE', "%{$search}%")
                    ->orWhere('department', 'LIKE', "%{$search}%")
                    ->orWhere('yearLevel', 'LIKE', "%{$search}%");
            });
        }

        $students = $query->paginate(5)
            ->appends(['search' => $request->input('search')])
            ->through(fn($student) => [
                'id'         => $student->id,
                'studentId'  => $student->studentId,
                'fName'      => $student->fName,
                'lName'      => $student->lName,
                'program'    => $student->program,
                'department' => $student->department,
                'yearLevel'  => $student->yearLevel,
            ]);

        return Inertia::render('StudentInfos/Index', [
            'students' => $students,
            'search'   => $request->input('search')
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'studentId'  => 'required|integer|unique:student_infos,studentId',
            'fName'      => 'required|string',
            'lName'      => 'required|string',
            'program'    => 'required|string',
            'department' => 'required|string',
            'yearLevel'  => 'required|string',
        ]);

        StudentInfo::create($validated);

        return redirect()->route('student_infos.index')
            ->with('success', 'Student Info created!');
    }
}

<?php

namespace App\Services;

use App\Models\StudentInfo;

class StudentInfoService
{
    public function checkStudentID(string $studentId): array
    {
        $student = StudentInfo::where('studentId', $studentId)->first();

        if (!$student) {
            return [
                'error' => 'student not found',
            ];
        }

        return [
            'student' => $student,
        ];
    }

    public function storeStudentInfo(array $validatedData): void
    {
        StudentInfo::updateOrCreate(
            ['studentId' => $validatedData['studentId']],
            $validatedData
        );
    }
}

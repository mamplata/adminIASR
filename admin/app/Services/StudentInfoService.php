<?php

namespace App\Services;

use App\Models\StudentInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class StudentInfoService
{
    public function checkStudentID(string $studentId): array
    {
        $student = StudentInfo::where('studentId', $studentId)->first();

        if (!$student) {
            // Attempt to fetch from external API if not found locally
            try {
                $externalResponse = $this->fetchStudentFromExternalAPI($studentId);
            } catch (\Exception $e) {
                return ['error' => 'Student not found.'];
            }
            if (isset($externalResponse['error'])) {
                return ['error' => 'Student not found.'];
            }
            $externalResponse['from_external'] = true;
            return $externalResponse;
        }

        // Validate last_enrolled_at format
        if (!preg_match('/(1st|2nd)\s(\d{4})/', $student->last_enrolled_at, $matches)) {
            return [
                'error' => 'Invalid last_enrolled_at format.',
            ];
        }

        $enrolledSemester = $matches[1];
        $enrolledYear = $matches[2];

        // Check if the student is enrolled in the current semester
        $currentSemester = DB::table('semesters')
            ->where('year', $enrolledYear)
            ->where('semester', $enrolledSemester)
            ->first();

        if (!$currentSemester) {
            // Attempt to fetch updated student data if there's a semester mismatch
            try {
                $externalResponse = $this->fetchStudentFromExternalAPI($studentId);
            } catch (\Exception $e) {
                return ['error' => 'Student not found.'];
            }
            if (isset($externalResponse['error'])) {
                return ['error' => 'Student not found.'];
            }
            $externalResponse['from_external'] = true;
            return $externalResponse;
        }

        return [
            'student' => $student,
            'from_external' => false,
        ];
    }


    /**
     * Fetch student data from an external API.
     */
    private function fetchStudentFromExternalAPI(string $studentId): array
    {
        $externalApiUrl = "http://127.0.0.1:8001/api/students/{$studentId}";

        $response = Http::timeout(5)->retry(3, 100)->get($externalApiUrl);

        if ($response->failed()) {
            return ['error' => 'Failed to fetch student data from external API.'];
        }

        $studentData = $response->json()['student'] ?? null;

        if (!$studentData) {
            return ['error' => 'Student not found in external API.'];
        }

        return ['student' => $studentData];
    }


    public function storeStudentInfo(array $validatedData): void
    {
        StudentInfo::updateOrCreate(
            ['studentId' => $validatedData['studentId']],
            $validatedData
        );
    }
}

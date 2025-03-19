<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterCards\StoreRegisteredCardRequest;
use App\Models\RegisteredCard;
use App\Models\Semester;
use App\Models\StudentInfo;
use App\Services\RegisteredCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class RegisteredCardController extends Controller
{
    protected $registeredCardService;

    public function __construct(RegisteredCardService $registeredCardService)
    {
        $this->registeredCardService = $registeredCardService;
    }

    public function index(Request $request)
    {
        $registeredCards = $this->registeredCardService->getRegisteredCards($request);

        return Inertia::render('RegisteredCards/Index', [
            'registeredCards' => $registeredCards,
            'search'          => $request->input('search')
        ]);
    }

    public function checkStudentID(Request $request)
    {
        $request->validate([
            'studentId' => 'required|exists:student_infos,studentId',
        ]);

        return response()->json($this->registeredCardService->checkStudentID($request->studentId));
    }

    public function checkCard(Request $request): array
    {
        $uid = $request->input('uid');

        // Check if the card exists in the registered cards table
        $registeredCard = RegisteredCard::where('uid', $uid)->first();
        if (!$registeredCard) {
            return [
                'exists' => false,
                'studentId' => null,
                'studentInfo' => null,
                'isEnrolled' => false,
                'message' => 'Card is not registered.',
            ];
        }

        // Get student info from local database
        $studentInfo = StudentInfo::where('studentId', $registeredCard->studentId)->first();
        $isEnrolled = false;
        $needsUpdate = false;

        if ($studentInfo) {
            if (preg_match('/(1st|2nd)\s(\d{4})/', $studentInfo->last_enrolled_at, $matches)) {
                [$semester, $year] = [$matches[1], $matches[2]];

                // Check if the student is enrolled in the current semester locally
                $isEnrolled = DB::table('semesters')
                    ->where('year', $year)
                    ->where('semester', $semester)
                    ->exists();

                // If not enrolled, mark for potential update
                if (!$isEnrolled) {
                    $needsUpdate = true;
                }
            }
        }

        // If student is not enrolled locally, check external API
        if ($needsUpdate) {
            try {
                $externalApiUrl = "http://127.0.0.1:8001/api/students/{$registeredCard->studentId}";

                $externalResponse = Http::timeout(5)->retry(3, 100)->get($externalApiUrl);

                if ($externalResponse->successful()) {
                    $externalStudent = $externalResponse->json()['student'];

                    if (preg_match('/(1st|2nd)\s(\d{4})/', $externalStudent['last_enrolled_at'], $matches)) {
                        [$semester, $year] = [$matches[1], $matches[2]];

                        // Check if the external semester is valid
                        $isEnrolled = DB::table('semesters')
                            ->where('year', $year)
                            ->where('semester', $semester)
                            ->exists();

                        // If semester is valid, update the local database
                        if ($isEnrolled) {
                            StudentInfo::updateOrCreate(
                                ['studentId' => $registeredCard->studentId],
                                $externalStudent
                            );

                            // Refresh student info
                            $studentInfo = StudentInfo::where('studentId', $registeredCard->studentId)->first();
                        }
                    }
                }
            } catch (\Exception $e) {
                return [
                    'exists' => true,
                    'studentId' => (string) $registeredCard->studentId,
                    'studentInfo' => $studentInfo,
                    'isEnrolled' => false,
                    'message' => 'Failed to retrieve student info from external API.',
                ];
            }
        }

        return [
            'exists' => true,
            'studentId' => (string) $registeredCard->studentId,
            'studentInfo' => $studentInfo,
            'isEnrolled' => $isEnrolled,
        ];
    }


    public function store(StoreRegisteredCardRequest $request)
    {
        $this->registeredCardService->storeOrUpdate($request->validated());

        return redirect()->route('registered-cards.index')
            ->with('success', 'Registered card updated or created successfully!');
    }

    public function destroy($id)
    {
        $this->registeredCardService->deleteCard($id);

        return redirect()->route('registered-cards.index')
            ->with('success', 'Registered card deleted successfully!');
    }

    public function scanCard(Request $request)
    {
        $uid = $request->input('uid');
        $data = $request->input('data');

        // Error 1: Unauthorized access.
        if (empty($data) || strlen($data) < 10) {
            return response()->json(['error' => 'Unauthorized access.'], 400);
        }

        // Split the data:
        // - First 3 digits for enrollment: first digit for semester, next 2 digits for year
        // - Remaining digits for student ID
        $enrollmentCode = substr($data, 0, 3);  // e.g. "225"
        $studentId      = substr($data, 3);      // e.g. "1901234"

        if (strlen($enrollmentCode) !== 3) {
            return response()->json(['error' => 'Unauthorized access.'], 400);
        }

        // Extract semester and year parts. 
        $semesterDigit = substr($enrollmentCode, 0, 1); // e.g. "2"
        $yearSuffix    = substr($enrollmentCode, 1);    // e.g. "25"

        // Convert the semester digit to its ordinal form (e.g. 2 -> "2nd")
        $semesterOrdinal = $this->convertToOrdinal($semesterDigit);

        // Fetch the semester record using the semester ordinal.
        $semesterRecord = Semester::where('semester', $semesterOrdinal)->first();

        // Convert the full year from the database (e.g., "2025") into its two-digit suffix.
        $dbYearSuffix = $semesterRecord ? substr($semesterRecord->year, -2) : null;

        // First, check if the UID is registered.
        $registeredCard = RegisteredCard::where('uid', $uid)->first();
        if (!$registeredCard) {
            return response()->json(['error' => 'Unauthorized access.'], 400);
        }


        // Next, check that the studentId matches the registered card
        // and that the enrollment details are valid.
        if ($registeredCard->studentId != $studentId || !$semesterRecord || $dbYearSuffix !== $yearSuffix) {
            return response()->json(['error' => 'Card is not activated'], 400);
        }

        // Fetch the student information.
        $student = StudentInfo::where('studentId', $studentId)->first();

        // Error: Missing student information.
        if (!$student) {
            return response()->json(['error' => 'No student information found, please inform admin.'], 404);
        }

        // Everything checks out. Return the student info along with enrollment details.
        return response()->json([
            'message'    => 'Card scanned and verified successfully.',
            'student'    => $student,
            'enrollment' => [
                'semester' => $semesterOrdinal,
                'year'     => $semesterRecord->year
            ]
        ], 200);
    }



    /**
     * Helper function to convert a single digit into its ordinal string.
     */
    private function convertToOrdinal($number)
    {
        if ($number == 1) return '1st';
        if ($number == 2) return '2nd';
    }
}

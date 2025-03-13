<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterCards\StoreRegisteredCardRequest;
use App\Models\RegisteredCard;
use App\Models\Semester;
use App\Models\StudentInfo;
use App\Services\RegisteredCardService;
use Illuminate\Http\Request;
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
        // Retrieve the 'uid' parameter from the request
        $uid = $request->input('uid');

        // Check if a registered card exists with the provided UID
        $registeredCard = RegisteredCard::where('uid', $uid)->first();
        $exists = $registeredCard !== null;

        // If the card exists, retrieve the associated student information
        $studentInfo = $exists ? StudentInfo::where('studentId', $registeredCard->studentId)->first() : null;

        // Return the response as an array
        return [
            'exists'      => $exists,
            'studentId'   => $exists ? (string) $registeredCard->studentId : null,
            'studentInfo' => $studentInfo,
            'message'     => $exists
                ? 'Card already registered with Student ID: ' . $registeredCard->studentId
                : 'Card is not registered.',
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

        // Check if the card is activated for the current semester and if it is registered.
        $registeredCard = RegisteredCard::where('uid', $uid)
            ->where('studentId', $studentId)
            ->first();

        if (!$semesterRecord || $dbYearSuffix !== $yearSuffix || !$registeredCard) {
            return response()->json(['error' => 'Card is not activated. Please activate it first by contacting the MSID team.'], 400);
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

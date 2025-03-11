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

        // Check if the card contains any data.
        if (empty($data)) {
            return response()->json(['error' => 'No data found on the card (unauthorized or empty).'], 400);
        }

        // Expecting card data in the format "2251901234"
        if (strlen($data) < 10) {
            return response()->json(['error' => 'Invalid card data format.'], 400);
        }

        // Split the data:
        // - First 3 digits for enrollment/semester check
        // - Remaining digits for student ID
        $semesterCode = substr($data, 0, 3);  // e.g. "225"
        $studentId   = substr($data, 3);       // e.g. "1901234"

        // Further split the semester code into its parts.
        if (strlen($semesterCode) !== 3) {
            return response()->json(['error' => 'Invalid semester code format.'], 400);
        }
        $yearDigit      = substr($semesterCode, 0, 1); // e.g. "2"
        $semesterNumber = substr($semesterCode, 1);    // e.g. "25"
        $yearOrdinal    = $this->convertToOrdinal($yearDigit); // converts 2 -> "2nd"

        // Check semester enrollment.
        // You could call an external API or use the Semester model directly.
        // Here, we use the Semester model to verify the semester code.
        $semesterRecord = Semester::where('semester', $semesterNumber)->first();
        if (!$semesterRecord) {
            return response()->json(['error' => 'Enrollment check failed: invalid semester code.'], 400);
        }

        // Check if the card is registered for the student.
        $registeredCard = RegisteredCard::where('uid', $uid)
            ->where('studentId', $studentId)
            ->first();
        if (!$registeredCard) {
            return response()->json(['error' => 'Card not registered for any student.'], 404);
        }

        // Fetch the student information.
        $student = StudentInfo::where('studentId', $studentId)->first();
        if (!$student) {
            return response()->json(['error' => 'Student not found or not currently enrolled.'], 404);
        }

        // Everything checks out. Return the student info along with enrollment details.
        return response()->json([
            'message'    => 'Card scanned and verified successfully.',
            'student'    => $student,
            'enrollment' => [
                'year'     => $yearOrdinal,
                'semester' => $semesterNumber
            ]
        ], 200);
    }

    /**
     * Helper function to convert a digit into an ordinal string.
     */
    private function convertToOrdinal($number)
    {
        if ($number == 1) return '1st';
        if ($number == 2) return '2nd';
    }
}

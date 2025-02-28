<?php

namespace App\Http\Controllers;

use App\Models\RegisteredCard;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RegisteredCardController extends Controller
{
    public function index(Request $request)
    {
        $query = RegisteredCard::orderBy('created_at', 'desc');

        // Optional search filtering by uid
        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('studentId', 'LIKE', "%{$search}%")
                    ->orWhere('uid', 'LIKE', "%{$search}%");
            });
        }

        $registeredCards = $query->paginate(5)
            ->appends(['search' => $request->input('search')])
            ->through(fn($card) => [
                'id'        => $card->id,
                'studentId' => $card->studentId,
                'uid'       => $card->uid,
            ]);

        return Inertia::render('RegisteredCards/Index', [
            'registeredCards' => $registeredCards,
            'search'          => $request->input('search')
        ]);
    }

    public function checkStudentID(Request $request)
    {
        // Validate that the studentId field is provided and that it exists in the student_infos table.
        $request->validate([
            'studentId' => 'required|exists:student_infos,studentId',
        ]);

        // Check if the studentId already exists in the registered_cards table.
        $exists = RegisteredCard::where('studentId', $request->studentId)->exists();

        // Return a JSON response with the result.
        if ($exists) {
            return response()->json([
                'exists'  => true,
                'message' => 'Student ID already exists in registered cards.'
            ]);
        } else {
            return response()->json([
                'exists'  => false,
                'message' => 'Student ID does not exist in registered cards.'
            ]);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'studentId' => 'required|exists:student_infos,studentId',
            'uid'       => [
                'required',
                'string',
                Rule::unique('registered_cards', 'uid')->where(function ($query) use ($request) {
                    // Only count UIDs that belong to a different student.
                    return $query->where('studentId', '<>', $request->studentId);
                }),
            ],
        ]);

        RegisteredCard::updateOrCreate(
            ['studentId' => $validated['studentId']], // search criteria
            ['uid' => $validated['uid']]              // fields to update or create
        );

        return redirect()->route('registered-cards.index')
            ->with('success', 'Registered card updated or created successfully!');
    }

    public function destroy($id)
    {
        $card = RegisteredCard::findOrFail($id);
        $card->delete();

        return redirect()->route('registered-cards.index')
            ->with('success', 'Registered card deleted successfully!');
    }
}

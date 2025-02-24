<?php

namespace App\Http\Controllers;

use App\Models\RegisteredCard;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegisteredCardController extends Controller
{
    public function index(Request $request)
    {
        $query = RegisteredCard::orderBy('created_at', 'desc');

        // Optional search filtering by uid
        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->input('search');
            $query->where('uid', 'LIKE', "%{$search}%");
        }

        $registeredCards = $query->paginate(5)
            ->appends(['search' => $request->input('search')])
            ->through(fn($card) => [
                'id'        => $card->id,
                'uid'       => $card->uid,
                'studentId' => $card->studentId,
            ]);

        return Inertia::render('RegisteredCards/Index', [
            'registeredCards' => $registeredCards,
            'search'          => $request->input('search')
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Assuming the student_infos table uses "studentId" as its primary key.
            'studentId' => 'required|exists:student_infos,studentId',
            'uid'       => 'required|string|unique:registered_cards,uid',
        ]);

        RegisteredCard::create($validated);

        return redirect()->route('registered_cards.index')
            ->with('success', 'Registered card created!');
    }
}

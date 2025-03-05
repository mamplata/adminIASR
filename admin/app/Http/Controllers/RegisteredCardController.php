<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterCards\StoreRegisteredCardRequest;
use App\Models\RegisteredCard;
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
}

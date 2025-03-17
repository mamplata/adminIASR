<?php

namespace App\Services;

use App\Models\RegisteredCard;
use Illuminate\Http\Request;

class RegisteredCardService
{
    public function getRegisteredCards(Request $request)
    {
        $query = RegisteredCard::orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('studentId', 'LIKE', "%{$search}%")
                    ->orWhere('uid', 'LIKE', "%{$search}%");
            });
        }

        return $query->paginate(5)
            ->appends(['search' => $request->input('search')])
            ->through(fn($card) => [
                'id'        => $card->id,
                'studentId' => $card->studentId,
                'uid'       => $card->uid,
            ]);
    }

    public function checkStudentID(string $studentId): array
    {
        $exists = RegisteredCard::where('studentId', $studentId)->exists();

        return [
            'exists'  => $exists,
            'message' => $exists
                ? 'Student ID already exists in registered cards.'
                : 'Student ID does not exist in registered cards.',
        ];
    }

    public function storeOrUpdate(array $validatedData): void
    {
        RegisteredCard::updateOrCreate(
            ['studentId' => $validatedData['studentId']],
            ['uid' => $validatedData['uid']]
        );
    }

    public function deleteCard(int $id): void
    {
        $card = RegisteredCard::findOrFail($id);
        $card->delete();
    }
}

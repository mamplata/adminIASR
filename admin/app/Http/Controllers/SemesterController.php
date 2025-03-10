<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SemesterController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $currentYear = now()->year;
        // Retrieve the first (and only) semester record, if it exists.
        $semester = Semester::first();

        return Inertia::render('Settings/Index', [
            'currentYear' => $currentYear,
            'semester' => $semester,
        ]);
    }

    /**
     * Store or update the semester setting.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', Rule::in([now()->year, now()->year - 1])],
            'semester' => 'required|string|in:1st,2nd',
        ]);

        // Check if a semester record already exists
        $semester = Semester::first();

        if ($semester) {
            // Update the existing record
            $semester->update($validated);
        } else {
            // Create a new record
            Semester::create($validated);
        }

        // Redirect back with a success flash message
        return redirect()->back()->with('success', 'Settings saved successfully!');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $query = Announcement::orderBy('created_at', 'desc');

        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('publisher', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('department')) {
            $query->where('department', $request->action);
        }

        $searchDepartments = Announcement::distinct()->pluck('department')->toArray();

        $announcements = $query->latest()
            ->paginate(5)
            ->appends(['search' => $request->input('search')])
            ->through(fn($user) => [
                'department' => $user->department,
                'publisher' => $user->publisher,
                'type' => $user->type,
                'content' => $user->content,
            ]);

        return Inertia::render('Announcements/Index', ['announcements' => $announcements, 'searchDepartments' => $searchDepartments, 'search' => $request->input('search')]);
    }

    public function create()
    {
        return Inertia::render('Announcements/Create');
    }

    public function store(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'publisher'  => 'required|string',
            'department' => 'nullable|string',
            'type'       => 'required|in:text,image',
            'content'    => 'required|array',
        ]);
    
        // Create a new announcement with the validated data
        Announcement::create($validatedData);
    
        return redirect()->route('announcements.index')
                         ->with('success', 'Announcement created successfully.');
    }
    
    public function show(Announcement $announcement)
    {
        return Inertia::render('Announcements/Show', [
            'announcement' => $announcement,
        ]);
    }

    public function edit(Announcement $announcement)
    {
        return Inertia::render('Announcements/Edit', [
            'announcement' => $announcement,
        ]);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'department' => 'nullable|string',
            'publisher'  => 'required|string',
            'type'       => 'required|in:text,image',
            'content'    => 'required|array',
        ]);

        // Conditional validation for content
        if ($validated['type'] === 'text') {
            Validator::make($validated['content'], [
                'title' => 'required|string',
                'body'  => 'required|string',
            ])->validate();
        } elseif ($validated['type'] === 'image') {
            Validator::make($validated['content'], [
                'image_url' => 'required|url',
                'caption'   => 'nullable|string',
            ])->validate();
        }

        $announcement->update($validated);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return redirect()->route('announcements.index')
            ->with('success', 'Announcement deleted successfully.');
    }
}

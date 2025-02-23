<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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
            ->through(fn ($user) => [
                'department' => $user->department,
                'publisher' => $user->publisher,
                'type' => $user->type,
                'content' => $user->content,
            ]);

        return Inertia::render('Announcements/Index', ['announcements' => $announcements, 'searchDepartments' => $searchDepartments, 'search' => $request->input('search')]);
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'department' => 'required|string',
            'publisher' => 'required|string',
            'type' => 'required|in:text,image', // Ensures type is valid
            'publication_date' => 'required|date',
            'content' => 'required', // Can be either JSON for text or a file
        ]);

        if ($request->type === 'image' && $request->hasFile('content')) {
            // Handle file upload
            $file = $request->file('content');
            $filePath = $file->store('announcements', 'public');
            Log::info('Stored file path: ' . $filePath);  // Log the file path
            $validated['content'] = [
                'file_name' => $file->getClientOriginalName(),
                'file_path' => Storage::url($filePath),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ];
        } else {
            $content = json_decode($request->input('content'), true);
            if (!$content || !isset($content['title']) || !isset($content['body'])) {
                return back()->withErrors(['content' => 'Invalid content format.']);
            }

            $validated['content'] = [
                'title' => $content['title'],
                'body'  => $content['body'],
            ];
        }

        Announcement::create($validated);

        return redirect()->route('announcements.index')->with('success', 'Announcement created!');
    }
}

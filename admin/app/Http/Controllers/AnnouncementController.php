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
            $query->where('department', $request->department);
        }

        $searchDepartments = Announcement::distinct()->pluck('department')->toArray();

        $announcements = $query->latest()
            ->paginate(5)
            ->appends(['search' => $request->input('search')])
            ->through(fn ($user) => [
                'id' => $user->id,
                'department' => $user->department,
                'publisher' => $user->publisher,
                'type' => $user->type,
                'content' => $user->content,
                'publication_date' => $user->publication_date->format('l, F j, Y g:i A'),
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

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'department'       => 'required|string',
            'publisher'        => 'required|string',
            'type'             => 'required|in:text,image',
            'publication_date' => 'required|date',
            'content'          => 'required',
            'file'             => 'nullable'
        ]);

        if ($request->type === 'image') {
            // Check if the user has uploaded a new file.
            if ($request->hasFile('content')) {
                // New file is provided, so delete the old file if it exists.
                if (isset($announcement->content['file_path'])) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $announcement->content['file_path']));
                }

                // Store the new file.
                $file = $request->file('file');
                $filePath = $file->store('announcements', 'public');
                $validated['content'] = [
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => Storage::url($filePath),
                    'mime_type' => $file->getClientMimeType(),
                    'size'      => $file->getSize(),
                ];
            } else {
                // No new file was uploaded.
                // Retain the existing file information from the database.
                $validated['content'] = $announcement->content;
            }
        } else {
            // For text type announcements, decode the JSON content.
            $content = json_decode($request->input('content'), true);
            if (!$content || !isset($content['title']) || !isset($content['body'])) {
                return back()->withErrors(['content' => 'Invalid content format.']);
            }
            $validated['content'] = [
                'title' => $content['title'],
                'body'  => $content['body'],
            ];
        }

        $announcement->update($validated);

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Announcement updated!');
     
    }
        
}


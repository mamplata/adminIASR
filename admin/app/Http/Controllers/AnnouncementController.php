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

        if ($request->filled('start_date') && $request->filled('end_date')) {
            // Both dates provided: filter between start and end
            $query->whereBetween('publication_date', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        } elseif ($request->filled('start_date')) {
            // Only start_date provided: filter records from the start_date onward
            $query->where('publication_date', '>=', $request->start_date . ' 00:00:00');
        } elseif ($request->filled('end_date')) {
            // Only end_date provided: filter records up to the end_date
            $query->where('publication_date', '<=', $request->end_date . ' 23:59:59');
        }


        $announcements = $query->latest()
            ->paginate(5)
            ->appends(['search' => $request->input('search')])
            ->through(fn($announcement) => [
                'id'                => $announcement->id,
                'department'        => $announcement->department,
                'publisher'         => $announcement->publisher,
                'type'              => $announcement->type,
                'content'           => $announcement->content,
                'publication_date'  => $announcement->publication_date->format('l, F j, Y'),
            ]);

        // Example return using Inertia.js
        return inertia('Announcements/Index', [
            'announcements'     => $announcements,
            'searchDepartments' => $searchDepartments,
        ]);
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
        // Base validation rules
        $rules = [
            'department'       => 'required|string',
            'publisher'        => 'required|string',
            'type'             => 'required|in:text,image',
            'publication_date' => 'required|date',
            'content'          => 'required', // For text type, this is JSON
        ];

        // For image type, if a new file is provided, add file-specific validations.
        if ($request->type === 'image') {
            if ($request->hasFile('content')) {
                // Validate the file (adjust mimes and max size as needed)
                $rules['content'] = 'required|file|mimes:jpeg,png,jpg|max:2048';
            }
            // No additional rules for file_path, as we might not send a new file.
        }

        $validated = $request->validate($rules);

        if ($request->type === 'image') {
            if ($request->hasFile('content')) {
                // Delete the previous file if it exists (even if coming from a text-to-image switch)
                $currentContent = $announcement->content;
                if (is_array($currentContent) && isset($currentContent['file_path'])) {
                    $relativePath = str_replace('/storage/', '', $currentContent['file_path']);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
                // Handle new file upload
                $file = $request->file('content');
                $filePath = $file->store('announcements', 'public');
                $validated['content'] = [
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => Storage::url($filePath),
                    'mime_type' => $file->getClientMimeType(),
                    'size'      => $file->getSize(),
                ];
            } else {
                // No new file uploaded; do not update the content.
                unset($validated['content']);
            }
        } else {
            // Changing to text type
            // If the previous content was an image, delete the stored image.
            $currentContent = $announcement->content;
            if (is_array($currentContent) && isset($currentContent['file_path'])) {
                $relativePath = str_replace('/storage/', '', $currentContent['file_path']);
                if (Storage::disk('public')->exists($relativePath)) {
                    Storage::disk('public')->delete($relativePath);
                }
            }
            // For text announcements, decode the JSON content.
            $content = json_decode($request->input('content'), true);
            if (!$content || !isset($content['title']) || !isset($content['body'])) {
                return back()->withErrors(['content' => 'Invalid content format.']);
            }
            $validated['content'] = [
                'title' => $content['title'],
                'body'  => $content['body'],
            ];
        }

        // Update the announcement with the validated data.
        $announcement->update($validated);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement updated!');
    }

    public function destroy(Announcement $announcement)
    {
        // Check if the current announcement has image content
        $currentContent = $announcement->content;
        if (is_array($currentContent) && isset($currentContent['file_path'])) {
            // Convert the full URL to the relative file path
            $relativePath = str_replace('/storage/', '', $currentContent['file_path']);
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }

        // Delete the announcement record from the database
        $announcement->delete();

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement deleted!');
    }
}

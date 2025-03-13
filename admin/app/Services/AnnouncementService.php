<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class AnnouncementService
{
    /**
     * Fetch and filter announcements with search, date range, and pagination.
     */
    public function getAnnouncements(Request $request)
    {
        $query = Announcement::query()->orderBy('created_at', 'desc');

        // 🔹 Search by Publisher
        if ($request->filled('search')) {
            $query->where('publisher', 'LIKE', "%{$request->input('search')}%");
        }

        // 🔹 Filter by Departments
        if ($request->filled('departments')) {
            $query->whereRaw("FIND_IN_SET(?, REPLACE(departments, ' ', ''))", [$request->departments]);
        }

        // 🔹 Filter by Programs
        if ($request->filled('programs')) {
            $query->whereRaw("FIND_IN_SET(?, REPLACE(REPLACE(departments, ':', ','), ' ', ''))", [$request->programs]);
        }

        // 🔹 Date Range Filtering (Includes End Date)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('publication_date', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ])->where('end_date', '>=', now()); // Only show active announcements
        } elseif ($request->filled('start_date')) {
            $query->where('publication_date', '>=', $request->start_date . ' 00:00:00');
        } elseif ($request->filled('end_date')) {
            $query->where('end_date', '<=', $request->end_date . ' 23:59:59');
        }

        return $query->paginate(5)
            ->appends($request->only(['search', 'departments', 'programs', 'start_date', 'end_date']))
            ->through(fn ($announcement) => [
                'id'                => $announcement->id,
                'departments'       => $announcement->departments,
                'publisher'         => $announcement->publisher,
                'type'              => $announcement->type,
                'content'           => $announcement->content,
                'publication_date'  => $announcement->publication_date->format('l, F j, Y'),
                'end_date'          => Carbon::parse($announcement->end_date)->format('l, F j, Y'),
            ]);
    }

    /**
     * Get searchable Departments and Programs.
     */
    public function getSearchableData()
    {
        // Get unique departments
        $rawDepartments = Announcement::distinct()->pluck('departments')->toArray();
        $searchDepartments = collect($rawDepartments)
            ->flatMap(fn ($deptString) => explode(';', $deptString))
            ->map(fn ($dept) => strpos(trim($dept), ':') !== false ? trim(explode(':', $dept)[0]) : trim($dept))
            ->unique()
            ->values()
            ->toArray();

        // Get programs grouped by department
        $searchPrograms = collect($rawDepartments)
            ->flatMap(fn ($deptString) => explode(';', $deptString))
            ->flatMap(function ($entry) {
                $parts = explode(':', $entry);
                if (count($parts) < 2) return [];
                $department = trim($parts[0]);
                $programString = trim($parts[1]);

                return collect(explode(',', $programString))
                    ->map(fn ($program) => ['department' => $department, 'program' => trim($program)]);
            })
            ->groupBy('department')
            ->map(fn ($group) => $group->pluck('program')->unique()->values())
            ->toArray();

        return compact('searchDepartments', 'searchPrograms');
    }

    /**
     * Get all departments and their respective programs.
     */
    public function getDepartmentsWithPrograms()
    {
        $departments = Department::pluck('code')->toArray();

        $departmentPrograms = Department::with('programs')->get()->mapWithKeys(fn ($department) => [
            $department->code => $department->programs->pluck('code')->toArray()
        ])->toArray();

        return compact('departments', 'departmentPrograms');
    }

    /**
     * Create a new announcement with an end date.
     */
    public function create(array $data)
{
    $request = request();

    if ($data['type'] === 'image' && $request->hasFile('content')) {
        $file = $request->file('content');
        $filePath = $file->store('announcements', 'public');
        $data['content'] = [
            'file_name' => $file->getClientOriginalName(),
            'file_path' => Storage::url($filePath),
            'mime_type' => $file->getClientMimeType(),
            'size'      => $file->getSize(),
        ];
    } else {
        $content = json_decode($data['content'], true);
        if (!$content || !isset($content['title']) || !isset($content['body'])) {
            throw new \Exception('Invalid content format.');
        }
        $data['content'] = [
            'title' => $content['title'],
            'body'  => $content['body'],
        ];
    }

    // ✅ Ensure `end_date` is required and not null
    if (!isset($data['end_date']) || empty($data['end_date'])) {
        throw new \Exception('End date is required.');
    }

    Announcement::create($data);
}

public function update(Announcement $announcement, array $data)
{
    $request = request();

    if ($data['type'] === 'image') {
        if ($request->hasFile('content')) {
            $currentContent = $announcement->content;
            if (is_array($currentContent) && isset($currentContent['file_path'])) {
                $relativePath = str_replace('/storage/', '', $currentContent['file_path']);
                if (Storage::disk('public')->exists($relativePath)) {
                    Storage::disk('public')->delete($relativePath);
                }
            }
            $file = $request->file('content');
            $filePath = $file->store('announcements', 'public');
            $data['content'] = [
                'file_name' => $file->getClientOriginalName(),
                'file_path' => Storage::url($filePath),
                'mime_type' => $file->getClientMimeType(),
                'size'      => $file->getSize(),
            ];
        } else {
            unset($data['content']);
        }
    } else {
        $content = json_decode($data['content'], true);
        if (!$content || !isset($content['title']) || !isset($content['body'])) {
            throw new \Exception('Invalid content format.');
        }
        $data['content'] = [
            'title' => $content['title'],
            'body'  => $content['body'],
        ];
    }

    // ✅ Ensure `end_date` is required for updates as well
    if (!isset($data['end_date']) || empty($data['end_date'])) {
        throw new \Exception('End date is required.');
    }

    $announcement->update($data);
}

    /**
     * Delete an announcement and remove associated files if necessary.
     */
    public function delete(Announcement $announcement)
    {
        $currentContent = $announcement->content;
        if (is_array($currentContent) && isset($currentContent['file_path'])) {
            $relativePath = str_replace('/storage/', '', $currentContent['file_path']);
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }

        $announcement->delete();
    }
}

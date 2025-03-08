<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnouncementService
{
    /**
     * Fetch and filter announcements with search and pagination.
     */
    public function getAnnouncements(Request $request)
    {
        $query = Announcement::query()->orderBy('created_at', 'desc');

        // 🔹 Search by Publisher
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where('publisher', 'LIKE', "%{$search}%");
        }

        // 🔹 Filter by Departments
        if ($request->filled('departments')) {
            $searchDepartment = trim($request->departments);

            // ✅ Group department filters together
            $query->where(function ($q) use ($searchDepartment) {
                $q->whereRaw("FIND_IN_SET(?, REPLACE(departments, ' ', ''))", [$searchDepartment])
                    ->orWhere('departments', 'LIKE', "%$searchDepartment%");
            });
        }

        // 🔹 Filter by Programs
        if ($request->filled('programs')) {
            $searchProgram = trim($request->programs);

            // ✅ Group program filters together
            $query->where(function ($q) use ($searchProgram) {
                $q->whereRaw("FIND_IN_SET(?, REPLACE(REPLACE(departments, ':', ','), ' ', ''))", [$searchProgram])
                    ->orWhere('departments', 'LIKE', "%: %{$searchProgram}%");
            });
        }

        // 🔹 Date Range Filtering
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('publication_date', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        } elseif ($request->filled('start_date')) {
            $query->where('publication_date', '>=', $request->start_date . ' 00:00:00');
        } elseif ($request->filled('end_date')) {
            $query->where('publication_date', '<=', $request->end_date . ' 23:59:59');
        }

        // 🔹 Debugging: Check SQL Query if still not working
        // dd($query->toSql(), $query->getBindings());

        // 🔹 Paginate Results
        return $query->latest()
            ->paginate(5)
            ->appends($request->only(['search', 'departments', 'programs', 'start_date', 'end_date']))
            ->through(fn ($announcement) => [
                'id'                => $announcement->id,
                'departments'       => $announcement->departments,
                'publisher'         => $announcement->publisher,
                'type'              => $announcement->type,
                'content'           => $announcement->content,
                'publication_date'  => $announcement->publication_date->format('l, F j, Y'),
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
     * Create a new announcement.
     */
    public function create(array $data)
    {
        /** @var \Illuminate\Http\Request $request */
        $request = request();
        if ($data['type'] === 'image' && $request->hasFile('content')) {
            // Handle file upload for image type announcements.
            $file = $request->file('content');
            $filePath = $file->store('announcements', 'public');
            $data['content'] = [
                'file_name' => $file->getClientOriginalName(),
                'file_path' => Storage::url($filePath),
                'mime_type' => $file->getClientMimeType(),
                'size'      => $file->getSize(),
            ];
        } else {
            // For text announcements, expect JSON data with title and body.
            $content = json_decode($data['content'], true);
            if (!$content || !isset($content['title']) || !isset($content['body'])) {
                throw new \Exception('Invalid content format.');
            }
            $data['content'] = [
                'title' => $content['title'],
                'body'  => $content['body'],
            ];
        }

        Announcement::create($data);
    }

    /**
     * Update an existing announcement.
     */
    public function update(Announcement $announcement, array $data)
    {
        /** @var \Illuminate\Http\Request $request */
        $request = request();
        if ($data['type'] === 'image') {
            if ($request->hasFile('content')) {
                // Delete previous image if it exists.
                $currentContent = $announcement->content;
                if (is_array($currentContent) && isset($currentContent['file_path'])) {
                    $relativePath = str_replace('/storage/', '', $currentContent['file_path']);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
                // Upload new file.
                $file = $request->file('content');
                $filePath = $file->store('announcements', 'public');
                $data['content'] = [
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => Storage::url($filePath),
                    'mime_type' => $file->getClientMimeType(),
                    'size'      => $file->getSize(),
                ];
            } else {
                // If no new file is provided, do not update the content.
                unset($data['content']);
            }
        } else {
            // For text type announcements: if the previous type was image, delete the stored image.
            $currentContent = $announcement->content;
            if (is_array($currentContent) && isset($currentContent['file_path'])) {
                $relativePath = str_replace('/storage/', '', $currentContent['file_path']);
                if (Storage::disk('public')->exists($relativePath)) {
                    Storage::disk('public')->delete($relativePath);
                }
            }
            // Decode the JSON content.
            $content = json_decode($data['content'], true);
            if (!$content || !isset($content['title']) || !isset($content['body'])) {
                throw new \Exception('Invalid content format.');
            }
            $data['content'] = [
                'title' => $content['title'],
                'body'  => $content['body'],
            ];
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

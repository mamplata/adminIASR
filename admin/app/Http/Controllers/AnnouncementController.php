<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\Announcements\AnnouncementStoreRequest;
use App\Http\Requests\Announcements\AnnouncementUpdateRequest;
use App\Services\AnnouncementService;
use App\Models\Department;

class AnnouncementController extends Controller
{
    protected $announcementService;

    public function __construct(AnnouncementService $announcementService)
    {
        $this->announcementService = $announcementService;
    }

    public function index(Request $request)
    {
        $query = Announcement::orderBy('created_at', 'desc');

        // 🔹 Search by Publisher (if applicable)
        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('publisher', 'LIKE', "%{$search}%");
            });
        }

        // 🔹 Handle Departments Search
        if ($request->filled('departments')) {
            $searchDepartment = trim($request->departments);

            $query->where(function ($q) use ($searchDepartment) {
                $q->whereRaw("FIND_IN_SET(?, REPLACE(departments, ' ', ''))", [$searchDepartment])
                    ->orWhere('departments', 'LIKE', "%$searchDepartment%");
            });
        }

        // 🔹 Get Unique Departments for Filtering
        $rawDepartments = Announcement::distinct()->pluck('departments')->toArray();
        $searchDepartments = collect($rawDepartments)
            ->flatMap(function ($deptString) {
                // Split each string by semicolon
                return explode(';', $deptString);
            })
            ->map(function ($dept) {
                $dept = trim($dept);
                // If a colon exists, take only the text before it; otherwise, return the trimmed string
                return strpos($dept, ':') !== false ? trim(explode(':', $dept)[0]) : $dept;
            })
            ->unique()
            ->values()
            ->toArray();

        // Create searchPrograms grouped by department
        $searchPrograms = collect($rawDepartments)
            ->flatMap(function ($deptString) {
                // Split each announcement by semicolon to separate department:program pairs
                return explode(';', $deptString);
            })
            ->flatMap(function ($entry) {
                // Split by colon to separate department and program(s)
                $parts = explode(':', $entry);
                if (count($parts) < 2) {
                    return []; // Skip if there's no program part
                }
                $department = trim($parts[0]);
                $programString = trim($parts[1]);

                // Check if the program part contains commas
                if (strpos($programString, ',') !== false) {
                    // Split by comma and return a collection of department-program pairs
                    return collect(explode(',', $programString))
                        ->map(function ($program) use ($department) {
                            return [
                                'department' => $department,
                                'program'    => trim($program),
                            ];
                        });
                }

                // Otherwise return a single department-program pair
                return [
                    [
                        'department' => $department,
                        'program'    => $programString,
                    ]
                ];
            })
            ->groupBy('department')
            ->map(function ($group) {
                // Pluck unique programs for each department
                return $group->pluck('program')->unique()->values();
            })
            ->toArray();

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

        // 🔹 Paginate Results
        $announcements = $query->latest()
            ->paginate(5)
            ->appends(['search' => $request->input('search')])
            ->through(fn($announcement) => [
                'id'                => $announcement->id,
                'departments'       => $announcement->departments,
                'publisher'         => $announcement->publisher,
                'type'              => $announcement->type,
                'content'           => $announcement->content,
                'publication_date'  => $announcement->publication_date->format('l, F j, Y'),
            ]);

        // Get an array of department codes
        $departments = Department::pluck('code')->toArray();

        // Get an associative array where each department code maps to an array of program codes
        $departmentPrograms = Department::with('programs')->get()->mapWithKeys(function ($department) {
            return [
                $department->code => $department->programs->pluck('code')->toArray()
            ];
        })->toArray();

        return inertia('Announcements/Index', [
            'announcements'     => $announcements,
            'searchDepartments' => $searchDepartments,
            'searchPrograms' => $searchPrograms,
            'departments' => $departments,
            'departmentPrograms'    => $departmentPrograms,
            'search' =>  $request->input('search'),
            'filterDepartments' =>  $request->input('departments'),
            'start_date' =>  $request->input('start_date'),
            'end_date' =>  $request->input('end_date')
        ]);
    }

    public function store(AnnouncementStoreRequest $request)
    {
        $this->announcementService->create($request->validated());

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement created!');
    }

    public function update(AnnouncementUpdateRequest $request, Announcement $announcement)
    {
        $this->announcementService->update($announcement, $request->validated());

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement updated!');
    }

    public function destroy(Announcement $announcement)
    {
        $this->announcementService->delete($announcement);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement deleted!');
    }
}

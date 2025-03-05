<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\Announcements\AnnouncementStoreRequest;
use App\Http\Requests\Announcements\AnnouncementUpdateRequest;
use App\Services\AnnouncementService;

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
            ->flatMap(fn ($deptString) => explode(',', $deptString)) // Split by comma
            ->map(fn ($dept) => trim($dept)) // Trim spaces
            ->unique()
            ->values()
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
            ->through(fn ($announcement) => [
                'id'                => $announcement->id,
                'departments'       => $announcement->departments,
                'publisher'         => $announcement->publisher,
                'type'              => $announcement->type,
                'content'           => $announcement->content,
                'publication_date'  => $announcement->publication_date->format('l, F j, Y'),
            ]);

        return inertia('Announcements/Index', [
            'announcements'     => $announcements,
            'searchDepartments' => $searchDepartments,
            'search' =>  $request->input('search')
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

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

        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('publisher', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('departments')) {
            $query->where('departments', $request->departments);
        }

        $rawDepartments = Announcement::distinct()->pluck('departments')->toArray();

        $searchDepartments = collect($rawDepartments)
            ->flatMap(function ($deptString) {
                return explode(',', $deptString);
            })
            ->map(function ($dept) {
                return trim($dept);
            })
            ->unique()
            ->values()
            ->toArray();


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

        $announcements = $query->latest()
            ->paginate(5)
            ->appends(['search' => $request->input('search')])
            ->through(fn ($announcement) => [
                'id'                => $announcement->id,
                'departments'        => $announcement->departments,
                'publisher'         => $announcement->publisher,
                'type'              => $announcement->type,
                'content'           => $announcement->content,
                'publication_date'  => $announcement->publication_date->format('l, F j, Y'),
            ]);

        return inertia('Announcements/Index', [
            'announcements'     => $announcements,
            'searchDepartments' => $searchDepartments,
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

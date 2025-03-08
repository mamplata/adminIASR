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
        // Fetch announcements
        $announcements = $this->announcementService->getAnnouncements($request);

        // Fetch searchable department and program filters
        $searchData = $this->announcementService->getSearchableData();

        // Fetch departments and programs for dropdowns
        $departmentData = $this->announcementService->getDepartmentsWithPrograms();

        return inertia('Announcements/Index', array_merge([
            'announcements'     => $announcements,
            'search'            => $request->input('search'),
            'filterDepartments' => $request->input('departments'),
            'filterPrograms'    => $request->input('programs'),
            'start_date'        => $request->input('start_date'),
            'end_date'          => $request->input('end_date')
        ], $searchData, $departmentData));
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

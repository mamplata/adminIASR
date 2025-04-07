<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\Announcements\AnnouncementStoreRequest;
use App\Http\Requests\Announcements\AnnouncementUpdateRequest;
use App\Services\AnnouncementService;
use App\Models\Department;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class AnnouncementController extends Controller
{
    protected $announcementService;

    /**
     * Create a new controller instance.
     *
     * @param  AnnouncementService  $announcementService
     * @return void
     */
    public function __construct(AnnouncementService $announcementService)
    {
        $this->announcementService = $announcementService;
    }

    /**
     * Fetch announcements and related data for the announcements index page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
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

    /**
     * Store a newly created announcement in storage.
     *
     * @param  \App\Http\Requests\Announcements\AnnouncementStoreRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */

    public function store(AnnouncementStoreRequest $request)
    {
        $this->announcementService->create($request->validated());

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement created!');
    }

    /**
     * Update the specified announcement in storage.
     *
     * @param  \App\Http\Requests\Announcements\AnnouncementUpdateRequest  $request
     * @param  \App\Models\Announcement  $announcement
     * @return \Illuminate\Http\RedirectResponse
     */

    /**
     * Update the specified announcement in storage.
     *
     * @param  \App\Http\Requests\Announcements\AnnouncementUpdateRequest  $request
     * @param  \App\Models\Announcement  $announcement
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(AnnouncementUpdateRequest $request, Announcement $announcement)
    {
        $this->announcementService->update($announcement, $request->validated());

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement updated!');
    }

    /**
     * Delete the specified announcement in storage.
     *
     * @param  \App\Models\Announcement  $announcement
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Announcement $announcement)
    {
        $this->announcementService->delete($announcement);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement deleted!');
    }

    /**
     * Fetch all announcements sorted in descending order of their publication date.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAnnouncements()
    {
        $announcements = Announcement::orderBy('publication_date', 'desc')->get();

        return response()->json(["announcements" => $announcements]);
    }
}

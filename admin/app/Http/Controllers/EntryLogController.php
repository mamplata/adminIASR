<?php

namespace App\Http\Controllers;

use App\Models\EntryLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EntryLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Build the query and order by created_at descending.
        $query = EntryLog::orderBy('created_at', 'desc');

        // Filter by student_id (partial match).
        if ($request->filled('search')) {
            $query->where('student_id', 'like', '%' . $request->search . '%');
        }

        // Filter by time_type (e.g., "IN" or "OUT").
        if ($request->filled('time_type')) {
            $query->where('time_type', $request->time_type);
        }

        // Filter by status (e.g., "Success" or "Failure").
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range on created_at.
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [
                $request->date_from . ' 00:00:00',
                $request->date_to . ' 23:59:59'
            ]);
        } elseif ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        } elseif ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // Paginate the results with 5 records per page and transform each entry.
        $entryLogs = $query->paginate(5)
            ->appends($request->all())
            ->through(fn($entryLog) => [
                'id'         => $entryLog->id,
                'student_id' => $entryLog->student_id,
                'uid'        => $entryLog->uid,
                'time_type'  => $entryLog->time_type,
                'status'     => $entryLog->status,
                'failure_reason' => $entryLog->failure_reason ?? 'N/A',
                'timestamp'  => $entryLog->created_at->format('l, F j, Y, g:i A'),
            ]);

        return Inertia::render('Logs/EntryLogs/Index', [
            'entryLogs' => $entryLogs,
            'filters'   => $request->only('search', 'time_type', 'status', 'date_from', 'date_to'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'uid' => 'required|string',
            'student_id' => 'required|string',
            'time_type' => 'required|in:IN,OUT',
            'status' => 'required|in:Success,Failure',
            'failure_reason' => 'nullable|string',
        ]);

        $entryLog = EntryLog::create($request->all());

        return response()->json($entryLog, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(EntryLog $entryLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EntryLog $entryLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EntryLog $entryLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EntryLog $entryLog)
    {
        //
    }
}

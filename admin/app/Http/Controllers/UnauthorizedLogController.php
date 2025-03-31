<?php

namespace App\Http\Controllers;

use App\Models\UnauthorizedLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnauthorizedLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Build the query and order by created_at descending.
        $query = UnauthorizedLog::orderBy('created_at', 'desc');

        // Filter by time_type (e.g., "IN" or "OUT").
        if ($request->filled('time_type')) {
            $query->where('time_type', $request->time_type);
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
        $unauthorizedLogs = $query->paginate(5)
            ->appends($request->all())
            ->through(fn($unauthorizedLog) => [
                'id'         => $unauthorizedLog->id,
                'device_id'  => $unauthorizedLog->device_id,
                'uid'        => $unauthorizedLog->uid,
                'time_type'  => $unauthorizedLog->time_type,
                'reason'     => $unauthorizedLog->reason ?? 'N/A',
                'timestamp'  => $unauthorizedLog->created_at->format('l, F j, Y, g:i A'),
            ]);

        return Inertia::render('Logs/UnauthorizedLogs/Index', [
            'unauthorizedLogs' => $unauthorizedLogs,
            'filters'   => $request->only('time_type', 'date_from', 'date_to'),
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
            'time_type' => 'required|in:IN,OUT',
            'reason' => 'required|string',
        ]);

        $unauthorizedLog = UnauthorizedLog::create($request->all());

        return response()->json($unauthorizedLog, 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(UnauthorizedLog $unauthorizedLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UnauthorizedLog $unauthorizedLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UnauthorizedLog $unauthorizedLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnauthorizedLog $unauthorizedLog)
    {
        //
    }
}

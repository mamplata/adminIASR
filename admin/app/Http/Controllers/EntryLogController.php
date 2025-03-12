<?php

namespace App\Http\Controllers;

use App\Models\EntryLog;
use Illuminate\Http\Request;

class EntryLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            'timestamp' => 'required|date',
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

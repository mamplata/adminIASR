<?php

namespace App\Http\Controllers;

use App\Models\UnauthorizedLog;
use Illuminate\Http\Request;

class UnauthorizedLogController extends Controller
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
            'time_type' => 'required|in:IN,OUT',
            'timestamp' => 'required|date',
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

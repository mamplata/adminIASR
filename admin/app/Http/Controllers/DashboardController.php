<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\RegisteredCard;
use App\Models\EntryLog;
use App\Models\UnauthorizedLog;
use App\Models\Device;
use App\Models\AuditLog;

class DashboardController extends Controller
{
    /**
     * Dashboard index page
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        // Total registered cards/students
        $registeredCardsCount = RegisteredCard::count();

        // Total entry logs with time_type IN and OUT
        $entryLogsIn = EntryLog::where('time_type', 'IN')->count();
        $entryLogsOut = EntryLog::where('time_type', 'OUT')->count();

        // Total unauthorized logs with time_type IN and OUT
        $unauthorizedLogsIn = UnauthorizedLog::where('time_type', 'IN')->count();
        $unauthorizedLogsOut = UnauthorizedLog::where('time_type', 'OUT')->count();

        // Total registered devices
        $registeredDevicesCount = Device::count();

        // Most recent audit logs (fetching the last 10 records)
        $auditLogs = AuditLog::latest()->take(10)->get();

        // Return the data using Inertia to render the Vue component
        return Inertia::render('Dashboard', [
            'registeredCardsCount'   => $registeredCardsCount,
            'entryLogsIn'            => $entryLogsIn,
            'entryLogsOut'           => $entryLogsOut,
            'unauthorizedLogsIn'     => $unauthorizedLogsIn,
            'unauthorizedLogsOut'    => $unauthorizedLogsOut,
            'registeredDevicesCount' => $registeredDevicesCount,
            'auditLogs'              => $auditLogs,
        ]);
    }
}

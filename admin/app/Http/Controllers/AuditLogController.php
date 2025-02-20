<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('admin')->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->has('action') && $request->action != '') {
            $query->where('action', $request->action);
        }

        if ($request->has('model') && $request->model != '') {
            $query->where('model', $request->model);
        }

        if ($request->has('admin_id') && $request->admin_id != '') {
            $query->where('admin_id', $request->admin_id);
        }

        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Paginate results
        $auditLogs = $query->paginate(10);

        return Inertia::render('Logs/AuditLogs/Index', [
            'auditLogs' => $auditLogs,
            'filters' => $request->all()  // Pass the current filters back to the front-end
        ]);
    }
}

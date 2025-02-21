<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('admin:id,name')->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('model')) {
            $query->where('model', $request->model);
        }

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Paginate results
        $auditLogs = $query->paginate(5)->through(fn ($log) => [
            'id' => $log->id,
            'action' => $log->action,
            'model' => $log->model,
            'model_id' => $log->model_id,
            'details' => $log->details,
            'created_at' => $log->created_at,
            'updated_at' => $log->updated_at,
            'admin' => $log->admin?->name, // Return only the admin name
        ]);

        return Inertia::render('Logs/AuditLogs/Index', [
            'auditLogs' => $auditLogs,
            'filters' => $request->only(['action', 'model', 'admin_id', 'start_date', 'end_date'])
        ]);
    }
}

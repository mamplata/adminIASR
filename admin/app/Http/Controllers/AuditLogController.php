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

        // Apply dropdown selections
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('model')) {
            $query->where('model', $request->model);
        }

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            // Both dates provided: filter between start and end
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        } elseif ($request->filled('start_date')) {
            // Only start_date provided: filter records from the start_date onward
            $query->where('created_at', '>=', $request->start_date . ' 00:00:00');
        } elseif ($request->filled('end_date')) {
            // Only end_date provided: filter records up to the end_date
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        // Fetch distinct actions, models, and admins for dropdown selection
        $actions = AuditLog::distinct()->pluck('action')->toArray();
        $models = AuditLog::distinct()->pluck('model')->toArray();
        $admins = \App\Models\User::select('id', 'name')->get();

        // Paginate results
        $auditLogs = $query->paginate(5)->through(fn($log) => [
            'action' => $log->action,
            'model' => $log->model,
            'model_id' => $log->model_id,
            'details' => $log->details,
            'admin' => $log->admin?->name,
            'created' => $log->created_at->format('l, F j, Y g:i A') // Example output: "Friday, February 21, 2025 6:06 AM"
        ]);

        return Inertia::render('Logs/AuditLogs/Index', [
            'models' => array_values($models),
            'actions' => array_values($actions),
            'admins' => $admins,
            'auditLogs' => $auditLogs
        ]);
    }
}

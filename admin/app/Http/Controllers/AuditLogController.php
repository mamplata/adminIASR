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
    
        // Fetch distinct actions, models, and admins for dropdown selection
        $actions = AuditLog::distinct()->pluck('action')->toArray();
        $models = AuditLog::distinct()->pluck('model')->toArray();
        $admins = \App\Models\User::select('id', 'name')->get(); // Get admin list
    
        // Paginate results
        $auditLogs = $query->paginate(5)->through(fn ($log) => [
            'id' => $log->id,
            'action' => $log->action,
            'model' => $log->model,
            'model_id' => $log->model_id,
            'details' => $log->details,
            'admin' => $log->admin?->name, // Return only the admin name
        ]);
    
        return Inertia::render('Logs/AuditLogs/Index', [
            'models' => array_values($models), // Ensure it's an array
            'actions' => array_values($actions), // Ensure it's an array
            'admins' => $admins, // Send admins as an array
            'auditLogs' => $auditLogs
        ]);
    }    
}

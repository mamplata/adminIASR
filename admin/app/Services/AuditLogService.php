<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogService
{
    public function getAuditLogs($request)
    {
        $query = AuditLog::with('admin:id,name')->orderBy('created_at', 'desc');

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
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        } elseif ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date . ' 00:00:00');
        } elseif ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        $actions = AuditLog::distinct()->pluck('action')->toArray();
        $models = AuditLog::distinct()->pluck('model')->toArray();
        $admins = User::select('id', 'name')->get();

        $auditLogs = $query->paginate(5)->through(fn($log) => [
            'action' => $log->action,
            'model' => $log->model,
            'model_id' => $log->model_id,
            'details' => $log->details,
            'admin' => $log->admin?->name,
            'created' => $log->created_at->format('l, F j, Y')
        ]);

        return compact('actions', 'models', 'admins', 'auditLogs');
    }
}

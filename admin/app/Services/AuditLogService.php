<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;

class AuditLogService
{
    public function getAuditLogs($request)
    {
        $query = AuditLog::with('admin:id,name')->orderBy('created_at', 'desc');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by admin_name instead of admin_id
        if ($request->filled('admin_name')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('admin', function ($subQuery) use ($request) {
                    $subQuery->where('name', $request->admin_name);
                })->orWhere('admin_name', $request->admin_name);
            });
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

        // Search inside 'details' JSON field
        if ($request->filled('searchDetails')) {
            $search = $request->searchDetails;

            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_CONTAINS(details, '\"$search\"')")
                    ->orWhere('details', 'LIKE', "%$search%");
            });
        }

        $actions = AuditLog::distinct()->pluck('action')->toArray();
        $types = AuditLog::distinct()->pluck('type')->toArray();

        // Get unique list of admin names (from audit_logs & users)
        $auditAdminNames = AuditLog::whereNotNull('admin_name')->distinct()->pluck('admin_name')->toArray();
        $activeAdminNames = User::whereIn('id', AuditLog::whereNotNull('admin_id')->pluck('admin_id'))
            ->pluck('name')
            ->toArray();
        $admins = array_unique(array_merge($auditAdminNames, $activeAdminNames)); // Merge deleted & active admin names

        // Get min and max dates from audit logs
        $min_date = AuditLog::min('created_at');
        $max_date = AuditLog::max('created_at');

        $auditLogs = $query->paginate(5)->through(function ($log) {
            $details = $log->details;

            // If details is a JSON string, decode it; if it's an object, cast to array
            if (is_string($details)) {
                $details = json_decode($details, true) ?: [];
            } elseif (is_object($details)) {
                $details = (array) $details;
            }

            // Format the date fields if they exist in details
            foreach (['created_at', 'updated_at', 'publication_date'] as $field) {
                if (isset($details[$field]) && !empty($details[$field])) {
                    try {
                        $details[$field] = Carbon::parse($details[$field])->format('l, F j, Y');
                    } catch (\Exception $e) {
                        // If parsing fails, keep the original value
                    }
                }
            }

            return [
                'action'   => $log->action,
                'type'    => $log->type,
                'type_id' => $log->type_id,
                'admin'    => $log->admin ? $log->admin->name : $log->admin_name,
                'created'  => $log->created_at->format('l, F j, Y'),
                'details'  => $details,
            ];
        });

        return compact('actions', 'types', 'admins', 'auditLogs', 'min_date', 'max_date');
    }
}

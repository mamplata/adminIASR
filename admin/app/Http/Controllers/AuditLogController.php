<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuditLogs\AuditLogRequest;
use App\Models\AuditLog;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    protected $auditLogService;

    /**
     * Instantiate the controller.
     *
     * @param AuditLogService $auditLogService
     */
    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Show the audit logs for the given filters.
     *
     * @param AuditLogRequest $request
     * @return \Inertia\Response
     */
    public function index(AuditLogRequest $request)
    {
        $data = $this->auditLogService->getAuditLogs($request);

        return Inertia::render('Logs/AuditLogs/Index', [
            'types' => array_values($data['types']),
            'actions' => array_values($data['actions']),
            'admins' => $data['admins'],
            'auditLogs' => $data['auditLogs'],
            'action' =>  $request->input('action'),
            'type' =>  $request->input('type'),
            'admin_id' =>  $request->input('admin_id'),
            'start_date' =>  $request->input('start_date'),
            'end_date' =>  $request->input('end_date'),
            'searchDetails' =>  $request->input('searchDetails')
        ]);
    }

    /**
     * Export all audit logs.
     */
    public function export()
    {
        $auditLogs = AuditLog::with('admin:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                $details = $log->details;

                // Decode details if it's a JSON string.
                if (is_string($details)) {
                    $details = json_decode($details, true) ?: [];
                } elseif (is_object($details)) {
                    $details = (array) $details;
                }

                // Format date fields within the details array if they exist.
                foreach (['created_at', 'updated_at', 'publication_date'] as $field) {
                    if (isset($details[$field]) && !empty($details[$field])) {
                        try {
                            $details[$field] = Carbon::parse($details[$field])->format('l, F j, Y');
                        } catch (\Exception $e) {
                            // Leave the original value if parsing fails.
                        }
                    }
                }

                return [
                    'action'   => $log->action,
                    'type'     => $log->type,
                    'type_id'  => $log->type_id,
                    'admin'    => $log->admin ? $log->admin->name : $log->admin_name,
                    'timestamp' => $log->created_at->format('l, F j, Y, g:i A'),
                    'details'  => $details,
                ];
            });

        // Return data as JSON for now.
        // You can also implement CSV/Excel export logic here if needed.
        return response()->json([
            'auditLogs' => $auditLogs,
        ]);
    }
}

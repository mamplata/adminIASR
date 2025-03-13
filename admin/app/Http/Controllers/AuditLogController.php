<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuditLogs\AuditLogRequest;
use App\Services\AuditLogService;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function index(AuditLogRequest $request)
    {
        $data = $this->auditLogService->getAuditLogs($request);

        return Inertia::render('Logs/AuditLogs/Index', [
            'types' => array_values($data['types']),
            'actions' => array_values($data['actions']),
            'admins' => $data['admins'],
            'auditLogs' => $data['auditLogs'],
            'minDate' => $data['min_date'],
            'maxDate' => $data['max_date'],
            'action' =>  $request->input('action'),
            'type' =>  $request->input('type'),
            'admin_id' =>  $request->input('admin_id'),
            'start_date' =>  $request->input('start_date'),
            'end_date' =>  $request->input('end_date'),
            'searchDetails' =>  $request->input('searchDetails')
        ]);
    }
}

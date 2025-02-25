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
            'models' => array_values($data['models']),
            'actions' => array_values($data['actions']),
            'admins' => $data['admins'],
            'auditLogs' => $data['auditLogs']
        ]);
    }
}

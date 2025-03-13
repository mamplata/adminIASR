<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AuditObserver
{
    public function created(Model $model)
    {
        $this->logAudit('create', $model, $model->toArray());
    }

    public function updated(Model $model)
    {
        if ($model->isDirty(['password', 'remember_token'])) {
            return;
        }

        $changes = [];
        $humanReadableTimestamp = $model->updated_at->format('F j, Y, g:i a');

        foreach ($model->getChanges() as $attribute => $newValue) {
            if ($attribute === 'updated_at') {
                continue;
            }

            $oldValue = $model->getOriginal($attribute);
            if (is_array($oldValue)) {
                $oldValue = json_encode($oldValue);
            }
            if (is_array($newValue)) {
                $newValue = json_encode($newValue);
            }

            $changes[$attribute] = $oldValue . ' → ' . $newValue . ' at ' . $humanReadableTimestamp;
        }

        $this->logAudit('update', $model, $changes);
    }

    public function deleting(Model $model)
    {
        $admin = Auth::user();
        $adminId = $admin ? $admin->id : null;
        $adminName = $admin ? $admin->name : 'Deleted Admin';

        if ($adminId) {
            AuditLog::where('admin_id', $adminId)
                ->update(['admin_name' => $adminName]);
        }

        $this->logAudit('delete', $model, [
            'time' => 'Deleted at ' . Carbon::now()->format('F j, Y, g:i a')
        ], $adminName);
    }

    private function logAudit(string $action, Model $model, $details, $adminName = null)
    {
        $admin = Auth::user();
        $adminId = $admin ? $admin->id : null;
        $adminName = $adminName ?? ($admin ? $admin->name : 'System'); // Default to 'System' if no admin is logged in

        $logData = [
            'admin_id'   => $adminId,
            'admin_name' => $adminName,
            'action'     => $action,
            'type'      => class_basename($model),
            'type_id'   => $model->id,
            'details'    => $details,
        ];

        unset($logData['details']['id'], $logData['details']['created_at'], $logData['details']['updated_at']);

        // Log to database
        AuditLog::create([
            'admin_id'   => $logData['admin_id'],
            'admin_name' => $logData['admin_name'],
            'action'     => $logData['action'],
            'type'      => $logData['type'],
            'type_id'   => $logData['type_id'],
            'details' => json_encode($logData['details']),

        ]);

        // Log to storage/logs/laravel.log
        Log::channel('audit')->info('Audit Log:', $logData);
    }
}

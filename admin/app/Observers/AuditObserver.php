<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    public function created(Model $model)
    {
        $this->logAudit('create', $model, $model->toArray()); // Log full details
    }

    public function updated(Model $model)
    {
        // Skip logging if 'password' or 'remember_token' is being updated
        if ($model->isDirty(['password', 'remember_token'])) {
            return;
        }

        $changes = [];
        // You can still use a human-readable timestamp for reference if desired
        $humanReadableTimestamp = $model->updated_at->format('F j, Y, g:i a');

        foreach ($model->getChanges() as $attribute => $newValue) {
            // Skip updated_at entirely
            if ($attribute === 'updated_at') {
                continue;
            }

            $oldValue = $model->getOriginal($attribute);
            // Log old → new plus the time of change
            $changes[$attribute] = $oldValue . ' → ' . $newValue . ' at ' . $humanReadableTimestamp;
        }

        $this->logAudit('update', $model, $changes);
    }

    public function deleted(Model $model)
    {
        $this->logAudit('delete', $model, [
            'id' => $model->id
        ]);
    }

    private function logAudit(string $action, Model $model, $details)
    {
        $adminId = Auth::id(); // Get the authenticated admin ID

        if (!$adminId) {
            // If no authenticated user, handle it gracefully
            return;
        }

        AuditLog::create([
            'admin_id' => $adminId,          // Authenticated admin performing the action
            'action'   => $action,
            'model'    => class_basename($model), // Log only the model name
            'model_id' => $model->id,        // Store the model ID
            'details'  => json_encode($details),
        ]);
    }
}

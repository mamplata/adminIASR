<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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
            if ($attribute === 'updated_at') {
                continue;
            }

            $oldValue = $model->getOriginal($attribute);

            // Convert array values to strings if necessary
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
        $action = 'delete';

        // Capture the admin's ID before the model is deleted
        $adminId = Auth::id();

        // Log the deletion before the model is actually deleted
        $this->logAudit($action, $model, [
            'time' => $action . ' at ' . Carbon::now()->format('F j, Y, g:i a')
        ], $adminId);
    }

    private function logAudit(string $action, Model $model, $details, $adminId = null)
    {
        // Use the provided admin ID or fallback to Auth::id() (for non-user deletions)
        $adminId = $adminId ?? Auth::id();

        if (!$adminId) {
            // If no authenticated user, handle it gracefully
            return;
        }

        AuditLog::create([
            'admin_id' => $adminId ?? null,               // Authenticated admin performing the action
            'action'   => $action,
            'model'    => class_basename($model), // Log only the model name
            'model_id' => $model->id,             // Store the model ID
            'details'  => json_encode($details),
        ]);
    }
}

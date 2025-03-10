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
        $admin = Auth::user();
        $adminId = $admin ? $admin->id : null;
        $adminName = $admin ? $admin->name : 'Deleted Admin'; // Fallback if missing

        if ($adminId) {
            // Update all previous logs so they don't lose reference to admin_name
            AuditLog::where('admin_id', $adminId)
                ->update(['admin_name' => $adminName]);
        }

        // Log the deletion action
        $this->logAudit('delete', $model, [
            'time' => 'Deleted at ' . Carbon::now()->format('F j, Y, g:i a')
        ], $adminName);
    }


    /**
     * Log the audit details including admin ID and name.
     */
    private function logAudit(string $action, Model $model, $details, $adminName = null)
    {
        $adminId = Auth::id();

        if (!$adminId && $adminName) {
            // If no admin ID (because user was deleted), fallback to name only
            AuditLog::create([
                'admin_id'   => null, // The user is deleted, so we can't store an ID
                'admin_name' => $adminName, // Store the last known admin name
                'action'     => $action,
                'model'      => class_basename($model),
                'model_id'   => $model->id,
                'details'    => json_encode($details),
            ]);
            return;
        }

        AuditLog::create([
            'admin_id'   => $adminId,
            'admin_name' => $adminName,
            'action'     => $action,
            'model'      => class_basename($model),
            'model_id'   => $model->id,
            'details'    => json_encode($details),
        ]);
    }
}

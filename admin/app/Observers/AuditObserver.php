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

        // Check if the 'password' attribute is in the changes
        if ($model->isDirty('password')) {
            // Skip logging if the password is being updated
            return;
        }
        $this->logAudit('update', $model, $model->getChanges()); // Log only changes
    }

    public function deleted(Model $model)
    {
        // Adjust logged details as needed; example provided for models with an id and name
        $this->logAudit('delete', $model, [
            'id' => $model->id
        ]);
    }

    private function logAudit(string $action, Model $model, $details)
    {
        AuditLog::create([
            'admin_id' => Auth::id(),
            'action'   => $action,
            'model'    => class_basename($model), // Get only the model name
            'model_id' => $model->id,
            'details'  => json_encode($details),
        ]);
    }
}

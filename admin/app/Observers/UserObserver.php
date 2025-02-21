<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    public function created(User $user)
    {
        $this->logAudit('create', $user, $user->toArray()); // Log full details
    }

    public function updated(User $user)
    {
        $this->logAudit('update', $user, $user->getChanges()); // Log only changes
    }

    public function deleted(User $user)
    {
        $this->logAudit('delete', $user, [
            'id' => $user->id,
            'name' => $user->name
        ]); // Log only ID & name
    }

    private function logAudit($action, $model, $details)
    {
        AuditLog::create([
            'admin_id' => Auth::id(),
            'action' => $action,
            'model' => class_basename($model), // Get only the model name
            'model_id' => $model->id,
            'details' => json_encode($details)
        ]);
    }
}

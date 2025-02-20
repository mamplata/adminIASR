<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    public function created(User $user)
    {
        $this->logAudit('create', $user);
    }

    public function updated(User $user)
    {
        $this->logAudit('update', $user);
    }

    public function deleted(User $user)
    {
        $this->logAudit('delete', $user);
    }

    private function logAudit($action, $model)
    {
        AuditLog::create([
            'admin_id' => Auth::id(),
            'action' => $action,
            'model' => get_class($model),
            'model_id' => $model->id,
            'details' => json_encode($model->getChanges())
        ]);
    }
}

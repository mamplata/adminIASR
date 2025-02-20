<?php

namespace App\Listeners;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;

class LogAuthenticationEvent
{
    public function handleLogin(Login $event)
    {
        $this->logAudit('login', $event->user);
    }

    public function handleLogout(Logout $event)
    {
        $this->logAudit('logout', $event->user);
    }

    private function logAudit($action, $user)
    {
        AuditLog::create([
            'admin_id' => $user->id,
            'action' => $action,
            'model' => 'User',
            'model_id' => $user->id,
            'details' => null  // Login/Logout actions usually don't require extra details
        ]);
    }
}

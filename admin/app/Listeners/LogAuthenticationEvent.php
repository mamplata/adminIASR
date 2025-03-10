<?php

namespace App\Listeners;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Carbon;

class LogAuthenticationEvent
{
    public function handleLogin(Login $event)
    {
        $this->logAudit('login', $event->user);
    }

    public function handleLogout(Logout $event)
    {
        $user = $event->user;
        if ($user && $user->exists) {
            $this->logAudit('logout', $user);
        }
    }

    private function logAudit($action, $user)
    {
        AuditLog::create([
            'admin_id' => $user->id,
            'action' => $action,
            'model' => 'User',
            'model_id' => $user->id,
            'details' => json_encode([
                'time' => $action . ' at ' . now()->format('F j, Y, g:i a')
            ])
        ]);
    }
}

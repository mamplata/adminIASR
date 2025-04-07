<?php

namespace App\Listeners;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Carbon;

class LogAuthenticationEvent
{
    /**
     * Handle the Login event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handleLogin(Login $event)
    {
        $this->logAudit('login', $event->user);
    }

    /**
     * Handle the Logout event.
     *
     * @param  Logout  $event
     * @return void
     */
    public function handleLogout(Logout $event)
    {
        $user = $event->user;
        if ($user && $user->exists) {
            $this->logAudit('logout', $user);
        }
    }

    /**
     * Logs an audit event, given the action that was performed and the user who performed it.
     *
     * @param string $action
     * @param \App\Models\User $user
     */
    private function logAudit($action, $user)
    {
        AuditLog::create([
            'admin_id' => $user->id,
            'admin_name' => $user->name,
            'action' => $action,
            'type' => 'User',
            'type_id' => $user->id,
            'details' => json_encode([
                'time' => $action . ' at ' . now()->format('F j, Y, g:i a')
            ])
        ]);
    }
}

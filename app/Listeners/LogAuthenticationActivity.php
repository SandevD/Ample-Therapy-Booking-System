<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Events\Dispatcher;

/**
 * Records authentication events into the activity log (log_name = "auth").
 */
class LogAuthenticationActivity
{
    public function handleLogin(Login $event): void
    {
        activity('auth')
            ->causedBy($event->user)
            ->performedOn($event->user)
            ->event('login')
            ->log('Logged in');
    }

    public function handleLogout(Logout $event): void
    {
        if (! $event->user) {
            return;
        }

        activity('auth')
            ->causedBy($event->user)
            ->performedOn($event->user)
            ->event('logout')
            ->log('Logged out');
    }

    public function handleFailed(Failed $event): void
    {
        activity('auth')
            ->event('failed_login')
            ->withProperty('email', $event->credentials['email'] ?? null)
            ->log('Failed login attempt');
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        activity('auth')
            ->causedBy($event->user)
            ->performedOn($event->user)
            ->event('password_reset')
            ->log('Password reset');
    }

    /**
     * @return array<class-string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
            Failed::class => 'handleFailed',
            PasswordReset::class => 'handlePasswordReset',
        ];
    }
}

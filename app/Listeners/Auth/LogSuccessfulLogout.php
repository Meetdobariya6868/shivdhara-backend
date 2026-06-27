<?php

declare(strict_types=1);

namespace App\Listeners\Auth;

use App\Events\Auth\UserLoggedOut;
use Illuminate\Support\Facades\Log;

final class LogSuccessfulLogout
{
    public function handle(UserLoggedOut $event): void
    {
        Log::info('[Auth] User logged out', [
            'user_id' => $event->user->id,
            'name'    => $event->user->name,
            'role'    => $event->user->role->value,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Listeners\Auth;

use App\Events\Auth\UserLoggedIn;
use Illuminate\Support\Facades\Log;

final class LogSuccessfulLogin
{
    public function handle(UserLoggedIn $event): void
    {
        Log::info('[Auth] User logged in', [
            'user_id' => $event->user->id,
            'name'    => $event->user->name,
            'role'    => $event->user->role->value,
        ]);
    }
}

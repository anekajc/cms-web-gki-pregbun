<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Routes a held user may still reach (the password page itself + logout);
     * everything else redirects them back to the password page until they set
     * their own password and/or pick a username.
     */
    private const ALLOWED = ['password.edit', 'password.update', 'logout'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $mustFinishSetup = $user && ($user->must_change_password || $user->username === null);

        if ($mustFinishSetup && ! in_array($request->route()?->getName(), self::ALLOWED, true)) {
            return redirect()->route('password.edit');
        }

        return $next($request);
    }
}

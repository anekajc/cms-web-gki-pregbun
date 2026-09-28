<?php

namespace App\Http\Middleware;

use App\Support\Access;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasAccess
{
    /**
     * Usage: ->middleware('access:dashboard') for "any section of the page", or
     * 'access:dashboard.warta' for one section. Page visits the user can't open
     * are bounced to the first page they can; mutations go back with an error.
     */
    public function handle(Request $request, Closure $next, string $key): Response
    {
        $user = $request->user();

        if ($user && $user->canAccessAny($key)) {
            return $next($request);
        }

        if (! $request->isMethod('GET')) {
            return back()->withErrors(['access' => 'Anda tidak memiliki akses untuk tindakan ini.']);
        }

        $landing = $user ? Access::landingRoute($user) : 'login';

        return redirect()->route($landing === $request->route()?->getName() ? 'no-access' : $landing);
    }
}

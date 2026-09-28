<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use App\Support\ActivityActions;
use App\Support\ActivityRecorder;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Writes one Log Aktivitas entry per successful CMS change. Field diffs come
 * from the ActivityRecorder, which models fill during the request.
 */
class RecordActivity
{
    public function __construct(private ActivityRecorder $recorder) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->recorder->reset();

        $response = $next($request);

        $action = ActivityActions::for($request->route()?->getName());
        $user = $request->user();

        if ($action && $user && ! $request->isMethodSafe() && $this->succeeded($request, $response)) {
            [$menu, $label] = $action;

            ActivityLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'menu' => $menu,
                'action' => $label,
                'subject' => $this->recorder->subject() ?? $this->routeSubject($request),
                'changes' => $this->recorder->changes() ?: null,
                'image_url' => $this->recorder->imageUrl(),
            ]);
        }

        return $response;
    }

    /**
     * Controllers report failures (validation, access denied, caps) by flashing
     * `errors`. Only errors flashed by *this* request count — `_flash.new` —
     * so a leftover flash from an earlier request can't hide a real change.
     */
    private function succeeded(Request $request, Response $response): bool
    {
        if ($response->getStatusCode() >= 400) {
            return false;
        }

        return ! in_array('errors', $request->session()->get('_flash.new', []), true);
    }

    /**
     * Fallback subject for changes no model event reported (e.g. reorders):
     * the first route-bound model that knows its own label.
     */
    private function routeSubject(Request $request): ?string
    {
        foreach ($request->route()?->parameters() ?? [] as $parameter) {
            if ($parameter instanceof Model && method_exists($parameter, 'activityLabel')) {
                return $parameter->activityLabel();
            }
        }

        return null;
    }
}

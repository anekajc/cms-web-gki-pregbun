<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Support\ActivityActions;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    /** Staff pick dates in local (WIB) time; timestamps are stored in UTC. */
    private const LOCAL_TIMEZONE = 'Asia/Jakarta';

    public function index(Request $request)
    {
        $filters = $request->validate([
            'user' => 'nullable|integer',
            'menu' => 'nullable|string|max:50',
            'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d',
            'q' => 'nullable|string|max:255',
        ]);

        $logs = ActivityLog::query()
            ->when($filters['user'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($filters['menu'] ?? null, fn ($query, $menu) => $query->where('menu', $menu))
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->where(
                'created_at', '>=', Carbon::parse($from, self::LOCAL_TIMEZONE)->startOfDay()->utc(),
            ))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->where(
                'created_at', '<=', Carbon::parse($to, self::LOCAL_TIMEZONE)->endOfDay()->utc(),
            ))
            ->when($filters['q'] ?? null, function ($query, $q) {
                // lower() + like works on both Postgres and the SQLite test DB.
                $term = '%'.mb_strtolower($q).'%';
                $query->where(fn ($inner) => $inner
                    ->whereRaw('lower(subject) like ?', [$term])
                    ->orWhereRaw('lower(action) like ?', [$term])
                    ->orWhereRaw('lower(user_name) like ?', [$term]));
            })
            ->latest()
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('activity-log', [
            'logs' => $logs,
            'filters' => [
                'user' => isset($filters['user']) ? (string) $filters['user'] : '',
                'menu' => $filters['menu'] ?? '',
                'from' => $filters['from'] ?? '',
                'to' => $filters['to'] ?? '',
                'q' => $filters['q'] ?? '',
            ],
            'users' => User::orderBy('name')->get(['id', 'name']),
            'menus' => ActivityActions::menuLabels(),
        ]);
    }
}

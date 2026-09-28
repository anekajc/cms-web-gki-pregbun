<?php

namespace App\Support;

use App\Models\Kebaktian;
use App\Models\Pelayanan;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Registry of per-user CMS access keys.
 *
 * Every page is a parent key ("dashboard"); its sections are leaf keys
 * ("dashboard.warta"). Only leaf keys are stored in user_permissions — a page
 * with no sections (Tentang Kami, Komisi) is its own leaf. Route gates check a
 * prefix via User::canAccessAny(), so "access:dashboard" means "any section".
 */
class Access
{
    /**
     * Pages in sidebar order: key => [label, landing route name, static children].
     * Ibadah and Pelayanan get their children from the database in tree().
     */
    private const PAGES = [
        'dashboard' => ['Dashboard', 'dashboard', [
            'dashboard.warta' => 'Warta Jemaat',
            'dashboard.video' => 'Video Home',
        ]],
        'tentangkami' => ['Tentang Kami', 'tentangkami', []],
        'ibadah' => ['Ibadah', 'kebaktian', [
            'ibadah.home' => 'Tampilan Home',
        ]],
        'event' => ['Event', 'event', [
            'event.mingguan' => 'Rutin Mingguan',
            'event.spesial' => 'Event Spesial',
        ]],
        'pelayanan' => ['Pelayanan', 'pelayanan', []],
        'bajem' => ['Bajem Benowo', 'bajem-benowo', [
            'bajem.tentang' => 'Tentang',
            'bajem.ibadah' => 'Jadwal Ibadah',
            'bajem.pelayanan' => 'Pelayanan',
            'bajem.lokasi' => 'Lokasi',
        ]],
        'komisi' => ['Komisi', 'komisi', []],
        'pembangunan' => ['Pembangunan', 'pembangunan', [
            'pembangunan.update' => 'Update',
            'pembangunan.dana' => 'Dana Pembangunan',
        ]],
        'persembahan' => ['Persembahan', 'persembahan', [
            'persembahan.hero' => 'Gambar Hero',
            'persembahan.item' => 'Daftar Item',
            'persembahan.pembangunan' => 'Pembangunan',
        ]],
        'master' => ['Master', 'master.pelayanan', [
            'master.pelayanan' => 'Pelayanan',
        ]],
    ];

    /**
     * The full checkbox tree, including one child per kebaktian and per
     * pelayanan row.
     *
     * @return list<array{key: string, label: string, leaf: bool, children: list<array{key: string, label: string}>}>
     */
    public static function tree(): array
    {
        $dynamic = [
            'ibadah' => Kebaktian::orderBy('order')->get(['id', 'title'])
                ->mapWithKeys(fn ($k) => ["ibadah.kebaktian.{$k->id}" => $k->title])->all(),
            'pelayanan' => Pelayanan::orderBy('order')->orderBy('id')->get(['id', 'title'])
                ->mapWithKeys(fn ($p) => ["pelayanan.{$p->id}" => $p->title])->all(),
        ];

        $tree = [];

        foreach (self::PAGES as $key => [$label, , $children]) {
            $children = [...$children, ...($dynamic[$key] ?? [])];

            $tree[] = [
                'key' => $key,
                'label' => $label,
                // A page with no sections is stored as its own key. Ibadah and
                // Pelayanan never are, even if their tables happen to be empty.
                'leaf' => $children === [] && ! isset($dynamic[$key]),
                'children' => array_map(
                    fn ($childKey, $childLabel) => ['key' => $childKey, 'label' => $childLabel],
                    array_keys($children),
                    array_values($children),
                ),
            ];
        }

        return $tree;
    }

    /**
     * Every storable leaf key.
     *
     * @return list<string>
     */
    public static function allKeys(): array
    {
        $keys = [];

        foreach (self::tree() as $page) {
            if ($page['leaf']) {
                $keys[] = $page['key'];
            }

            foreach ($page['children'] as $child) {
                $keys[] = $child['key'];
            }
        }

        return $keys;
    }

    /**
     * Route name of the first page (in sidebar order) the user can open.
     */
    public static function landingRoute(User $user): string
    {
        foreach (self::PAGES as $key => [, $route]) {
            if ($user->canAccessAny($key)) {
                return $route;
            }
        }

        return 'no-access';
    }

    /**
     * Abort the current mutation unless the signed-in user holds $key (or a key
     * beneath it). Used for checks that depend on the record being edited.
     */
    public static function ensure(string ...$keys): void
    {
        $user = request()->user();

        foreach ($keys as $key) {
            if (! $user || ! $user->canAccessAny($key)) {
                self::deny();
            }
        }
    }

    public static function deny(string $message = 'Anda tidak memiliki akses untuk tindakan ini.'): never
    {
        throw new HttpResponseException(back()->withErrors(['access' => $message]));
    }
}

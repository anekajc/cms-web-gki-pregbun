import { DatePicker } from '@/components/date-picker';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { format } from 'date-fns';
import { id as localeId } from 'date-fns/locale';
import { ChevronDown, ChevronLeft, ChevronRight, ChevronUp, RotateCcw } from 'lucide-react';
import { useEffect, useState } from 'react';

type Value = string | number | null | Value[];

interface Change {
    label: string;
    old: Value;
    new: Value;
}

interface LogEntry {
    id: number;
    user_name: string;
    user_username: string | null;
    menu: string;
    action: string;
    subject: string | null;
    changes: Change[] | null;
    image_url: string | null;
    created_at: string;
}

interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

interface Filters {
    user: string;
    menu: string;
    from: string;
    to: string;
    q: string;
}

interface Props {
    logs: Paginated<LogEntry>;
    filters: Filters;
    users: { id: number; name: string }[];
    menus: Record<string, string>;
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Log Aktivitas', href: '/log-aktivitas' }];

// Radix Select can't use '' as an item value, so "Semua" is a sentinel.
const ALL = 'all';

export default function ActivityLogPage({ logs, filters, users, menus }: Props) {
    const [q, setQ] = useState(filters.q);

    const apply = (patch: Partial<Filters>) => {
        const next = { ...filters, ...patch };
        // Drop empty filters so the URL stays clean.
        const params = Object.fromEntries(Object.entries(next).filter(([, v]) => v !== ''));
        router.get(route('activity-log'), params, { preserveState: true, preserveScroll: true, replace: true });
    };

    // Search as the admin types, debounced so each keystroke isn't a request.
    useEffect(() => {
        if (q === filters.q) return;
        const timer = setTimeout(() => apply({ q }), 400);
        return () => clearTimeout(timer);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [q]);

    const hasFilters = Object.values(filters).some((v) => v !== '');

    const reset = () => {
        setQ('');
        router.get(route('activity-log'), {}, { preserveState: true, replace: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Log Aktivitas" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">Log Aktivitas</h1>
                    <p className="text-muted-foreground text-sm">
                        Riwayat perubahan konten oleh setiap pengguna. Gunakan untuk menelusuri siapa yang mengubah apa.
                    </p>
                </div>

                <Card>
                    <CardContent className="grid gap-4 p-4 md:grid-cols-2 lg:grid-cols-5">
                        <div className="grid gap-2">
                            <Label htmlFor="filter-user">User</Label>
                            <Select value={filters.user || ALL} onValueChange={(v) => apply({ user: v === ALL ? '' : v })}>
                                <SelectTrigger id="filter-user">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={ALL}>Semua user</SelectItem>
                                    {users.map((u) => (
                                        <SelectItem key={u.id} value={String(u.id)}>
                                            {u.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="filter-menu">Menu</Label>
                            <Select value={filters.menu || ALL} onValueChange={(v) => apply({ menu: v === ALL ? '' : v })}>
                                <SelectTrigger id="filter-menu">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={ALL}>Semua menu</SelectItem>
                                    {Object.entries(menus).map(([key, label]) => (
                                        <SelectItem key={key} value={key}>
                                            {label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="filter-from">Dari</Label>
                            <DatePicker id="filter-from" value={filters.from} onChange={(from) => apply({ from })} placeholder="Semua tanggal" />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="filter-to">Sampai</Label>
                            <DatePicker id="filter-to" value={filters.to} onChange={(to) => apply({ to })} placeholder="Semua tanggal" />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="filter-q">Cari</Label>
                            <div className="flex gap-2">
                                <Input id="filter-q" value={q} onChange={(e) => setQ(e.target.value)} placeholder="Item, aksi, nama, atau username" />
                                {hasFilters && (
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="icon"
                                        onClick={reset}
                                        aria-label="Reset filter"
                                        title="Reset filter"
                                    >
                                        <RotateCcw className="h-4 w-4" />
                                    </Button>
                                )}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {logs.data.length > 0 ? (
                    <div className="space-y-3">
                        {logs.data.map((entry) => (
                            <LogCard key={entry.id} entry={entry} menuLabel={menus[entry.menu] ?? entry.menu} />
                        ))}
                    </div>
                ) : (
                    <div className="text-muted-foreground rounded-lg border border-dashed py-10 text-center text-sm">
                        {hasFilters ? 'Tidak ada aktivitas yang cocok dengan filter.' : 'Belum ada aktivitas.'}
                    </div>
                )}

                {logs.last_page > 1 && (
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <span className="text-muted-foreground text-sm">
                            Halaman {logs.current_page} dari {logs.last_page} ({logs.total} aktivitas)
                        </span>
                        <div className="flex gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                disabled={!logs.prev_page_url}
                                aria-label="Sebelumnya"
                                onClick={() => logs.prev_page_url && router.get(logs.prev_page_url, {}, { preserveState: true })}
                            >
                                <ChevronLeft className="h-4 w-4" /> <span className="hidden sm:inline">Sebelumnya</span>
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                disabled={!logs.next_page_url}
                                aria-label="Berikutnya"
                                onClick={() => logs.next_page_url && router.get(logs.next_page_url, {}, { preserveState: true })}
                            >
                                <span className="hidden sm:inline">Berikutnya</span> <ChevronRight className="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

function LogCard({ entry, menuLabel }: { entry: LogEntry; menuLabel: string }) {
    const [open, setOpen] = useState(false);
    const changes = entry.changes ?? [];

    return (
        <Card>
            <CardContent className="space-y-3 p-4">
                <div className="flex flex-wrap items-start justify-between gap-2">
                    <div className="min-w-0 space-y-1">
                        <div className="flex flex-wrap items-center gap-2 text-sm">
                            <span className="font-semibold">{entry.user_name}</span>
                            {entry.user_username && <span className="font-mono text-xs text-muted-foreground">@{entry.user_username}</span>}
                            <Badge variant="secondary">{menuLabel}</Badge>
                        </div>
                        <p className="text-sm">
                            {entry.action}
                            {entry.subject && (
                                <>
                                    {' — '}
                                    <span className="font-medium">{entry.subject}</span>
                                </>
                            )}
                        </p>
                    </div>
                    <span className="text-muted-foreground text-xs whitespace-nowrap">
                        {format(new Date(entry.created_at), 'd MMM yyyy, HH:mm', { locale: localeId })}
                    </span>
                </div>

                {entry.image_url && (
                    <a href={entry.image_url} target="_blank" rel="noreferrer" className="inline-block">
                        <img src={entry.image_url} alt="Gambar baru" className="h-20 rounded-md border object-cover" />
                    </a>
                )}

                {changes.length > 0 && (
                    <div>
                        <Button type="button" variant="ghost" size="sm" className="-ml-2 h-7 px-2" onClick={() => setOpen((v) => !v)}>
                            {open ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />}
                            {open ? 'Sembunyikan perubahan' : `Lihat perubahan (${changes.length})`}
                        </Button>

                        {open && (
                            <div className="mt-2 overflow-x-auto rounded-lg border">
                                <table className="w-full text-sm">
                                    <thead className="bg-muted/50 text-muted-foreground border-b text-left">
                                        <tr>
                                            <th className="w-40 px-3 py-2 font-medium">Field</th>
                                            <th className="px-3 py-2 font-medium">Sebelum</th>
                                            <th className="px-3 py-2 font-medium">Sesudah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {changes.map((change, index) => (
                                            <tr key={index} className="border-b align-top last:border-0">
                                                <td className="px-3 py-2 font-medium">{change.label}</td>
                                                <td className="text-muted-foreground px-3 py-2">
                                                    <ValueView value={change.old} />
                                                </td>
                                                <td className="px-3 py-2">
                                                    <ValueView value={change.new} />
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

/** Renders a recorded value: lists as bullets, image URLs as thumbnails, links as links. */
function ValueView({ value }: { value: Value }) {
    if (value === null || value === '') {
        return <span className="text-muted-foreground">—</span>;
    }

    if (Array.isArray(value)) {
        return (
            <ul className="list-disc space-y-0.5 pl-4">
                {value.map((item, index) => (
                    <li key={index}>
                        <ValueView value={item} />
                    </li>
                ))}
            </ul>
        );
    }

    const text = String(value);

    if (/^https?:\/\//.test(text)) {
        if (text.includes('/image/upload/')) {
            return (
                <a href={text} target="_blank" rel="noreferrer" className="inline-block">
                    <img src={text} alt="" className="h-16 rounded border object-cover" />
                </a>
            );
        }
        return (
            <a href={text} target="_blank" rel="noreferrer" className="text-primary break-all underline underline-offset-2">
                {text}
            </a>
        );
    }

    return <span className="break-words whitespace-pre-wrap">{text}</span>;
}

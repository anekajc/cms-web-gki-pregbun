import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { format } from 'date-fns';
import { id as localeId } from 'date-fns/locale';
import { Check, Copy, Ellipsis, Eye, EyeOff, KeyRound, ShieldCheck, Trash2, UserPlus } from 'lucide-react';
import { useState } from 'react';

interface UserRow {
    id: number;
    name: string;
    username: string | null;
    email: string;
    role: 'admin' | 'user';
    generated_password: string | null;
    created_at: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'User',
        href: '/user',
    },
];

const formatCreated = (iso: string) => format(new Date(iso), 'd MMM yyyy', { locale: localeId });

export default function UserIndex({ users }: { users: UserRow[] }) {
    const currentUserId = usePage<SharedData>().props.auth.user.id;
    const [revealed, setRevealed] = useState<Set<number>>(new Set());
    const [copiedId, setCopiedId] = useState<number | null>(null);

    const toggleReveal = (id: number) =>
        setRevealed((prev) => {
            const next = new Set(prev);
            if (next.has(id)) {
                next.delete(id);
            } else {
                next.add(id);
            }
            return next;
        });

    const copyPassword = async (id: number, password: string) => {
        await navigator.clipboard.writeText(password);
        setCopiedId(id);
        setTimeout(() => setCopiedId((curr) => (curr === id ? null : curr)), 1500);
    };

    const regenerate = (u: UserRow) => {
        if (confirm(`Buat password baru untuk ${u.name}? Password lama tidak akan berlaku lagi.`)) {
            router.post(route('user.regenerate', u.id), {}, { preserveScroll: true });
        }
    };

    const remove = (u: UserRow) => {
        if (confirm(`Hapus akun ${u.name} (${u.email})? Tindakan ini tidak dapat dibatalkan.`)) {
            router.delete(route('user.destroy', u.id), { preserveScroll: true });
        }
    };

    const passwordCell = (u: UserRow) => (
        <PasswordCell
            password={u.generated_password}
            revealed={revealed.has(u.id)}
            copied={copiedId === u.id}
            onToggle={() => toggleReveal(u.id)}
            onCopy={() => copyPassword(u.id, u.generated_password!)}
        />
    );

    const actions = (u: UserRow) => <UserActions user={u} isSelf={u.id === currentUserId} onRegenerate={regenerate} onRemove={remove} />;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="User" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div className="min-w-0">
                        <h1 className="text-2xl font-bold tracking-tight">User</h1>
                        <p className="text-sm text-muted-foreground">Kelola akun pengguna. Kirim password yang dibuat ke pengguna terkait.</p>
                    </div>
                    <Button asChild>
                        <Link href={route('user.create')}>
                            <UserPlus className="h-4 w-4" /> Tambah User
                        </Link>
                    </Button>
                </div>

                {/* Mobile: one card per user */}
                <div className="space-y-3 md:hidden">
                    {users.map((u) => (
                        <Card key={u.id}>
                            <CardContent className="space-y-3 p-4">
                                <div className="flex items-start justify-between gap-2">
                                    <div className="min-w-0 space-y-0.5">
                                        <p className="font-medium">
                                            {u.name}
                                            {u.id === currentUserId && <span className="ml-2 text-xs text-muted-foreground">(Anda)</span>}
                                        </p>
                                        {u.username && <p className="font-mono text-sm text-muted-foreground">@{u.username}</p>}
                                        <p className="text-sm break-all text-muted-foreground">{u.email}</p>
                                    </div>
                                    <div className="flex shrink-0 items-center gap-1">
                                        <Badge variant={u.role === 'admin' ? 'default' : 'secondary'}>{u.role === 'admin' ? 'Admin' : 'User'}</Badge>
                                        {actions(u)}
                                    </div>
                                </div>
                                <dl className="grid grid-cols-[auto_1fr] items-center gap-x-4 gap-y-1 text-sm">
                                    <dt className="text-muted-foreground">Password</dt>
                                    <dd className="min-w-0">{passwordCell(u)}</dd>
                                    <dt className="text-muted-foreground">Dibuat</dt>
                                    <dd>{formatCreated(u.created_at)}</dd>
                                </dl>
                            </CardContent>
                        </Card>
                    ))}
                    {users.length === 0 && (
                        <div className="rounded-lg border border-dashed py-10 text-center text-sm text-muted-foreground">Belum ada pengguna.</div>
                    )}
                </div>

                {/* Desktop: table */}
                <Card className="hidden md:block">
                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead className="border-b text-left text-muted-foreground">
                                    <tr>
                                        <th className="px-4 py-3 font-medium">Nama</th>
                                        <th className="px-4 py-3 font-medium">Username</th>
                                        <th className="px-4 py-3 font-medium">Email</th>
                                        <th className="px-4 py-3 font-medium">Peran</th>
                                        <th className="px-4 py-3 font-medium">Password</th>
                                        <th className="px-4 py-3 font-medium">Dibuat</th>
                                        <th className="px-4 py-3 text-right font-medium">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {users.map((u) => (
                                        <tr key={u.id} className="border-b last:border-0">
                                            <td className="px-4 py-3 font-medium">
                                                {u.name}
                                                {u.id === currentUserId && <span className="ml-2 text-xs text-muted-foreground">(Anda)</span>}
                                            </td>
                                            <td className="px-4 py-3 text-muted-foreground">
                                                {u.username ? (
                                                    <span className="font-mono">@{u.username}</span>
                                                ) : (
                                                    <span title="Belum dibuat — diminta saat login berikutnya">—</span>
                                                )}
                                            </td>
                                            <td className="px-4 py-3 text-muted-foreground">{u.email}</td>
                                            <td className="px-4 py-3">
                                                <Badge variant={u.role === 'admin' ? 'default' : 'secondary'}>{u.role === 'admin' ? 'Admin' : 'User'}</Badge>
                                            </td>
                                            <td className="px-4 py-3">{passwordCell(u)}</td>
                                            <td className="px-4 py-3 text-muted-foreground">{formatCreated(u.created_at)}</td>
                                            <td className="px-4 py-3">
                                                <div className="flex justify-end">{actions(u)}</div>
                                            </td>
                                        </tr>
                                    ))}
                                    {users.length === 0 && (
                                        <tr>
                                            <td colSpan={7} className="px-4 py-10 text-center text-muted-foreground">
                                                Belum ada pengguna.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

function PasswordCell({
    password,
    revealed,
    copied,
    onToggle,
    onCopy,
}: {
    password: string | null;
    revealed: boolean;
    copied: boolean;
    onToggle: () => void;
    onCopy: () => void;
}) {
    if (!password) {
        return <span className="text-muted-foreground">—</span>;
    }

    return (
        <div className="flex items-center gap-2">
            <span className={`font-mono ${revealed ? '' : 'blur-sm select-none'}`}>{password}</span>
            <Button
                type="button"
                variant="ghost"
                size="icon"
                className="h-7 w-7"
                onClick={onToggle}
                aria-label={revealed ? 'Sembunyikan password' : 'Tampilkan password'}
            >
                {revealed ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
            </Button>
            <Button type="button" variant="ghost" size="icon" className="h-7 w-7" onClick={onCopy} aria-label="Salin password">
                {copied ? <Check className="h-4 w-4 text-green-600" /> : <Copy className="h-4 w-4" />}
            </Button>
        </div>
    );
}

function UserActions({
    user,
    isSelf,
    onRegenerate,
    onRemove,
}: {
    user: UserRow;
    isSelf: boolean;
    onRegenerate: (u: UserRow) => void;
    onRemove: (u: UserRow) => void;
}) {
    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button type="button" variant="ghost" size="icon" className="h-8 w-8" aria-label="Aksi">
                    <Ellipsis className="h-4 w-4" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-52">
                {user.role === 'admin' ? (
                    <DropdownMenuItem disabled className="items-start">
                        <ShieldCheck className="mt-0.5" />
                        <div>
                            <div>Set Pemakai</div>
                            <div className="text-xs text-muted-foreground">Admin memiliki akses penuh</div>
                        </div>
                    </DropdownMenuItem>
                ) : (
                    <DropdownMenuItem asChild>
                        <Link href={route('user.access.edit', user.id)}>
                            <ShieldCheck /> Set Pemakai
                        </Link>
                    </DropdownMenuItem>
                )}
                <DropdownMenuItem onSelect={() => onRegenerate(user)}>
                    <KeyRound /> Password Baru
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem onSelect={() => onRemove(user)} disabled={isSelf} className="text-destructive focus:text-destructive">
                    <Trash2 className="text-destructive" /> Hapus
                    {isSelf && <span className="ml-auto text-xs text-muted-foreground">Akun Anda</span>}
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

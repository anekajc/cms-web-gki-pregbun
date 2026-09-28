import InputError from '@/components/input-error';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useRef } from 'react';

import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pengaturan password',
        href: '/settings/password',
    },
];

export default function Password() {
    const { user } = usePage<SharedData>().props.auth;
    const mustChange = user.must_change_password ?? false;
    // EnsurePasswordChanged holds users here until both are done.
    const needsUsername = !user.username;
    // Existing accounts that only lack a username just pick one; no password change.
    const usernameOnly = needsUsername && !mustChange;

    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);

    const { data, setData, errors, put, reset, processing, recentlySuccessful } = useForm({
        username: '',
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const updatePassword: FormEventHandler = (e) => {
        e.preventDefault();

        put(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => reset(),
            onError: (errors) => {
                if (errors.password) {
                    reset('password', 'password_confirmation');
                    passwordInput.current?.focus();
                }

                if (errors.current_password) {
                    reset('current_password');
                    currentPasswordInput.current?.focus();
                }
            },
        });
    };

    const notice = mustChange
        ? needsUsername
            ? 'Demi keamanan, ganti password yang diberikan admin dan buat username Anda sebelum melanjutkan.'
            : 'Demi keamanan, Anda harus mengganti password yang diberikan admin sebelum melanjutkan.'
        : needsUsername
          ? 'Sekarang Anda juga bisa login dengan username. Buat username Anda sebelum melanjutkan.'
          : null;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={usernameOnly ? 'Buat username' : 'Ganti password'} />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title={usernameOnly ? 'Buat username' : needsUsername ? 'Ganti password & buat username' : 'Ganti password'}
                        description={
                            usernameOnly
                                ? 'Username dapat dipakai untuk login selain email.'
                                : 'Gunakan password yang panjang dan sulit ditebak agar akun Anda tetap aman.'
                        }
                    />

                    {notice && (
                        <div className="rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200">
                            {notice}
                        </div>
                    )}

                    <form onSubmit={updatePassword} className="space-y-6">
                        {needsUsername && (
                            <div className="grid gap-2">
                                <Label htmlFor="username">Username</Label>

                                <Input
                                    id="username"
                                    value={data.username}
                                    onChange={(e) => setData('username', e.target.value.toLowerCase())}
                                    className="mt-1 block w-full"
                                    autoComplete="username"
                                    autoCapitalize="none"
                                    autoCorrect="off"
                                    spellCheck={false}
                                    placeholder="contoh: budi.santoso"
                                    autoFocus
                                />

                                <p className="text-xs text-muted-foreground">
                                    Huruf kecil, angka, titik (.), garis bawah (_), atau tanda hubung (-), 3–30 karakter, tanpa spasi.
                                </p>

                                <InputError message={errors.username} />
                            </div>
                        )}

                        {!usernameOnly && (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="current_password">Password saat ini</Label>

                                    <Input
                                        id="current_password"
                                        ref={currentPasswordInput}
                                        value={data.current_password}
                                        onChange={(e) => setData('current_password', e.target.value)}
                                        type="password"
                                        className="mt-1 block w-full"
                                        autoComplete="current-password"
                                        placeholder={mustChange ? 'Password dari admin' : 'Password saat ini'}
                                    />

                                    <InputError message={errors.current_password} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password">Password baru</Label>

                                    <Input
                                        id="password"
                                        ref={passwordInput}
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        type="password"
                                        className="mt-1 block w-full"
                                        autoComplete="new-password"
                                        placeholder="Password baru"
                                    />

                                    <InputError message={errors.password} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password_confirmation">Konfirmasi password baru</Label>

                                    <Input
                                        id="password_confirmation"
                                        value={data.password_confirmation}
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                        type="password"
                                        className="mt-1 block w-full"
                                        autoComplete="new-password"
                                        placeholder="Ulangi password baru"
                                    />

                                    <InputError message={errors.password_confirmation} />
                                </div>
                            </>
                        )}

                        <div className="flex items-center gap-4">
                            <Button disabled={processing}>{usernameOnly ? 'Simpan username' : 'Simpan'}</Button>

                            <Transition
                                show={recentlySuccessful}
                                enter="transition ease-in-out"
                                enterFrom="opacity-0"
                                leave="transition ease-in-out"
                                leaveTo="opacity-0"
                            >
                                <p className="text-sm text-neutral-600">Tersimpan</p>
                            </Transition>
                        </div>
                    </form>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}

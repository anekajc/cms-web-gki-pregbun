import InputError from '@/components/input-error';
import SortablePelayananList, { type MasterPelayananItem } from '@/components/master/sortable-pelayanan-list';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Master',
        href: '/master/pelayanan',
    },
    {
        title: 'Pelayanan',
        href: '/master/pelayanan',
    },
];

export default function MasterPelayananPage({ pelayanan }: { pelayanan: MasterPelayananItem[] }) {
    const { data, setData, post, processing, errors, reset } = useForm({ name: '' });
    const [busy, setBusy] = useState(false);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('master.pelayanan.store'), {
            preserveScroll: true,
            onSuccess: () => reset('name'),
        });
    };

    const rename = (id: number, name: string) => {
        setBusy(true);
        router.put(route('master.pelayanan.update', id), { name }, { preserveScroll: true, onFinish: () => setBusy(false) });
    };

    const reorder = (ids: number[]) => {
        setBusy(true);
        router.put(route('master.pelayanan.reorder'), { ids }, { preserveScroll: true, onFinish: () => setBusy(false) });
    };

    const destroy = (id: number) => {
        if (!confirm('Hapus pelayanan ini? Gambar dan kartu detailnya juga akan terhapus.')) return;
        setBusy(true);
        router.delete(route('master.pelayanan.destroy', id), { preserveScroll: true, onFinish: () => setBusy(false) });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Master - Pelayanan" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">Master Pelayanan</h1>
                    <p className="text-muted-foreground text-sm">
                        Tambah, ubah nama, hapus, dan atur urutan pelayanan yang tampil sebagai tab di halaman Pelayanan. Isi gambar, deskripsi, dan
                        kartu detail masing-masing tetap dikelola di halaman Pelayanan.
                    </p>
                </div>

                <Card>
                    <CardContent className="p-6">
                        <form onSubmit={submit} className="flex flex-col gap-3 sm:flex-row sm:items-end">
                            <div className="grid flex-1 gap-2">
                                <Label htmlFor="name">Nama Pelayanan Baru</Label>
                                <Input
                                    id="name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="mis. Pelayanan Lansia"
                                />
                                <InputError message={errors.name} />
                            </div>
                            <Button type="submit" disabled={processing || !data.name.trim()}>
                                <Plus className="h-4 w-4" /> Tambah
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-6">
                        {pelayanan.length > 0 ? (
                            <SortablePelayananList items={pelayanan} onReorder={reorder} onRename={rename} onDelete={destroy} busy={busy} />
                        ) : (
                            <div className="text-muted-foreground rounded-lg border border-dashed py-10 text-center text-sm">
                                Belum ada pelayanan. Tambahkan yang pertama di atas.
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

import InputError from '@/components/input-error';
import ImageCropperDialog from '@/components/kebaktian/image-cropper-dialog';
import SortableImageGrid from '@/components/kebaktian/sortable-image-grid';
import DetailCardsEditor, { type DetailItem, type DetailPayload } from '@/components/pelayanan/detail-cards-editor';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useAccess } from '@/hooks/use-access';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { ChangeEvent, FormEventHandler, useRef, useState } from 'react';

interface PelayananImage {
    id: number;
    url: string;
    public_id: string;
    order: number;
}

interface PelayananItem {
    id: number;
    slug: string;
    title: string;
    subtitle: string;
    description: string;
    order: number;
    images: PelayananImage[];
    details: DetailItem[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pelayanan',
        href: '/pelayanan',
    },
];

const ASPECT = 4 / 3;

export default function PelayananPage({ pelayanan: allPelayanan, maxImages }: { pelayanan: PelayananItem[]; maxImages: number }) {
    const { can } = useAccess();
    // Only the ministries this user was granted (pelayanan.{id}).
    const pelayanan = allPelayanan.filter((p) => can(`pelayanan.${p.id}`));
    const [activeSlug, setActiveSlug] = useState<string>(pelayanan[0]?.slug ?? '');
    const active = pelayanan.find((p) => p.slug === activeSlug) ?? pelayanan[0];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Pelayanan" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">Pelayanan</h1>
                    <p className="text-muted-foreground text-sm">
                        Kelola gambar dan informasi untuk tiap pelayanan. Tambah, ubah nama, hapus, atau atur urutannya di{' '}
                        <a href={route('master.pelayanan')} className="underline underline-offset-2">
                            Master &gt; Pelayanan
                        </a>
                        .
                    </p>
                </div>

                <div className="flex flex-wrap gap-1 border-b">
                    {pelayanan.map((p) => (
                        <button
                            key={p.slug}
                            onClick={() => setActiveSlug(p.slug)}
                            className={cn(
                                'border-b-2 px-4 py-2 text-sm font-medium transition-colors',
                                active?.slug === p.slug
                                    ? 'border-primary text-foreground'
                                    : 'text-muted-foreground hover:text-foreground border-transparent',
                            )}
                        >
                            {p.title}
                        </button>
                    ))}
                </div>

                {active ? (
                    <PelayananEditor key={active.id} item={active} maxImages={maxImages} />
                ) : (
                    <div className="text-muted-foreground rounded-lg border border-dashed py-10 text-center text-sm">
                        Belum ada pelayanan. Tambahkan di{' '}
                        <a href={route('master.pelayanan')} className="underline underline-offset-2">
                            Master &gt; Pelayanan
                        </a>
                        .
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

function PelayananEditor({ item, maxImages }: { item: PelayananItem; maxImages: number }) {
    const { data, setData, put, processing, errors, recentlySuccessful } = useForm({
        subtitle: item.subtitle,
        description: item.description,
    });

    const fileInputRef = useRef<HTMLInputElement>(null);
    const [cropSrc, setCropSrc] = useState<string | null>(null);
    const [uploading, setUploading] = useState(false);
    const [savingDetails, setSavingDetails] = useState(false);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('pelayanan.update', item.id), { preserveScroll: true });
    };

    const onPickFile = (e: ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) setCropSrc(URL.createObjectURL(file));
        e.target.value = ''; // allow picking the same file again
    };

    const onCropped = (blob: Blob) => {
        setUploading(true);
        router.post(
            route('pelayanan.images.store', item.id),
            { image: new File([blob], 'pelayanan.jpg', { type: 'image/jpeg' }) },
            {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => setCropSrc(null),
                onFinish: () => setUploading(false),
            },
        );
    };

    const reorder = (ids: number[]) => {
        router.put(route('pelayanan.images.reorder', item.id), { ids }, { preserveScroll: true });
    };

    const deleteImage = (id: number) => {
        if (confirm('Hapus gambar ini?')) {
            router.delete(route('pelayanan.images.destroy', id), { preserveScroll: true });
        }
    };

    const saveDetails = (payload: DetailPayload[]) => {
        setSavingDetails(true);
        router.put(route('pelayanan.details.sync', item.id), { details: payload }, { preserveScroll: true, onFinish: () => setSavingDetails(false) });
    };

    const atMax = item.images.length >= maxImages;

    return (
        <div className="grid gap-6 lg:grid-cols-3">
            <Card className="lg:col-span-3">
                <CardContent className="space-y-4 p-6">
                    <div className="flex items-center justify-between">
                        <h2 className="font-semibold">
                            Galeri Foto ({item.images.length}/{maxImages})
                        </h2>
                        <Button type="button" size="sm" disabled={atMax || uploading} onClick={() => fileInputRef.current?.click()}>
                            <Plus className="h-4 w-4" /> {uploading ? 'Mengunggah...' : 'Tambah Gambar'}
                        </Button>
                    </div>
                    <p className="text-muted-foreground text-sm">
                        Seret untuk mengatur urutan. Gambar pertama menjadi foto utama. {atMax && 'Batas gambar tercapai.'}
                    </p>

                    {item.images.length > 0 ? (
                        <SortableImageGrid images={item.images} onReorder={reorder} onDelete={deleteImage} />
                    ) : (
                        <div className="text-muted-foreground rounded-lg border border-dashed py-10 text-center text-sm">Belum ada gambar.</div>
                    )}

                    <input ref={fileInputRef} type="file" accept="image/*" className="hidden" onChange={onPickFile} />
                    <ImageCropperDialog
                        open={cropSrc !== null}
                        imageSrc={cropSrc}
                        aspect={ASPECT}
                        processing={uploading}
                        onClose={() => setCropSrc(null)}
                        onCropped={onCropped}
                    />
                </CardContent>
            </Card>

            <Card className="lg:col-span-3">
                <CardContent className="space-y-4 p-6">
                    <form onSubmit={submit} className="space-y-4">
                        <div className="grid gap-1">
                            <span className="text-sm font-medium">Nama</span>
                            <p className="text-muted-foreground text-sm">
                                {item.title} — ubah di{' '}
                                <a href={route('master.pelayanan')} className="underline underline-offset-2">
                                    Master &gt; Pelayanan
                                </a>
                                .
                            </p>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="subtitle">Subjudul</Label>
                            <Input
                                id="subtitle"
                                value={data.subtitle}
                                onChange={(e) => setData('subtitle', e.target.value)}
                                placeholder="Pendampingan pastoral & psikologis"
                                required
                            />
                            <InputError message={errors.subtitle} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="description">Deskripsi</Label>
                            <textarea
                                id="description"
                                rows={8}
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                                placeholder="Tekan Enter untuk paragraf baru."
                                className="border-input bg-background placeholder:text-muted-foreground focus-visible:ring-ring flex w-full rounded-md border px-3 py-2 text-sm shadow-xs focus-visible:ring-2 focus-visible:outline-hidden"
                            />
                            <InputError message={errors.description} />
                        </div>

                        <div className="flex items-center gap-3">
                            <Button type="submit" disabled={processing || !data.subtitle || !data.description}>
                                Simpan Informasi
                            </Button>
                            {recentlySuccessful && <span className="text-muted-foreground text-sm">Tersimpan</span>}
                        </div>
                    </form>
                </CardContent>
            </Card>

            <Card className="lg:col-span-3">
                <CardContent className="space-y-3 p-6">
                    <div>
                        <h3 className="font-semibold">Kartu Detail</h3>
                        <p className="text-muted-foreground text-sm">
                            Label tebal + isi (mis. KONSELOR, JADWAL, LOKASI). Seret untuk mengatur urutan.
                        </p>
                    </div>
                    <DetailCardsEditor details={item.details} onSave={saveDetails} saving={savingDetails} />
                </CardContent>
            </Card>
        </div>
    );
}

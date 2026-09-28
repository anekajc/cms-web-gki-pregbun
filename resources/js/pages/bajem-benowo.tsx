import SortableItemList from '@/components/bajem-benowo/sortable-item-list';
import InputError from '@/components/input-error';
import ImageCropperDialog from '@/components/kebaktian/image-cropper-dialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useAccess } from '@/hooks/use-access';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { ImagePlus, ListPlus, Plus, Trash2 } from 'lucide-react';
import { ChangeEvent, FormEventHandler, useRef, useState } from 'react';

interface Settings {
    id: number;
    about_description: string | null;
    about_image_public_id: string | null;
    about_image_url: string | null;
    pelayanan_intro: string | null;
    address: string | null;
    maps_url: string | null;
    map_embed_url: string | null;
    location_image_public_id: string | null;
    location_image_url: string | null;
}

interface Item {
    id: number;
    section: 'ibadah' | 'pelayanan';
    title: string;
    description: string | null;
    schedules: string[] | null;
    location: string | null;
    audience: string | null;
    cadence: string | null;
    image_public_id: string | null;
    image_url: string | null;
    order: number;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Bajem Benowo',
        href: '/bajem-benowo',
    },
];

const TEXTAREA_CLASS =
    'flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-hidden';

const TABS = [
    { key: 'tentang', title: 'Tentang' },
    { key: 'ibadah', title: 'Jadwal Ibadah' },
    { key: 'pelayanan', title: 'Pelayanan' },
    { key: 'lokasi', title: 'Lokasi' },
] as const;

type TabKey = (typeof TABS)[number]['key'];

export default function BajemBenowoPage({ settings, items }: { settings: Settings; items: { ibadah: Item[]; pelayanan: Item[] } }) {
    const { can } = useAccess();
    // Only the tabs this user was granted (bajem.tentang / .ibadah / .pelayanan / .lokasi).
    const tabs = TABS.filter((t) => can(`bajem.${t.key}`));
    const [tab, setTab] = useState<TabKey | undefined>(tabs[0]?.key);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Bajem Benowo" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">Bajem Benowo</h1>
                    <p className="text-muted-foreground text-sm">Kelola konten halaman Bajem Benowo di situs publik.</p>
                </div>

                <div className="flex flex-wrap gap-1 border-b">
                    {tabs.map((t) => (
                        <button
                            key={t.key}
                            onClick={() => setTab(t.key)}
                            className={cn(
                                'border-b-2 px-4 py-2 text-sm font-medium transition-colors',
                                tab === t.key ? 'border-primary text-foreground' : 'text-muted-foreground hover:text-foreground border-transparent',
                            )}
                        >
                            {t.title}
                        </button>
                    ))}
                </div>

                {tab === 'tentang' && <TentangEditor settings={settings} />}
                {tab === 'ibadah' && <ItemsManager section="ibadah" items={items.ibadah} />}
                {tab === 'pelayanan' && <ItemsManager section="pelayanan" items={items.pelayanan} intro={settings.pelayanan_intro} />}
                {tab === 'lokasi' && <LokasiEditor settings={settings} />}
            </div>
        </AppLayout>
    );
}

function TentangEditor({ settings }: { settings: Settings }) {
    const { data, setData, put, processing, errors, recentlySuccessful } = useForm({
        about_description: settings.about_description ?? '',
    });

    const fileInputRef = useRef<HTMLInputElement>(null);
    const [cropSrc, setCropSrc] = useState<string | null>(null);
    const [uploading, setUploading] = useState(false);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('bajem-benowo.settings.update'), { preserveScroll: true });
    };

    const onPickFile = (e: ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) setCropSrc(URL.createObjectURL(file));
        e.target.value = '';
    };

    const onCropped = (blob: Blob) => {
        setUploading(true);
        router.post(
            route('bajem-benowo.settings.image.update', 'about'),
            { image: new File([blob], 'about.jpg', { type: 'image/jpeg' }) },
            { forceFormData: true, preserveScroll: true, onSuccess: () => setCropSrc(null), onFinish: () => setUploading(false) },
        );
    };

    const removeImage = () => {
        if (confirm('Hapus gambar Tentang ini?')) {
            router.delete(route('bajem-benowo.settings.image.destroy', 'about'), { preserveScroll: true });
        }
    };

    return (
        <div className="grid gap-6 lg:grid-cols-3">
            <Card className="lg:col-span-1">
                <CardContent className="space-y-4 p-6">
                    <h2 className="font-semibold">Gambar</h2>
                    <div className="bg-muted aspect-video overflow-hidden rounded-lg border">
                        {settings.about_image_url ? (
                            <img src={settings.about_image_url} alt="" className="h-full w-full object-cover" />
                        ) : (
                            <div className="text-muted-foreground flex h-full items-center justify-center text-sm">Belum ada gambar</div>
                        )}
                    </div>
                    <div className="flex gap-2">
                        <Button type="button" size="sm" disabled={uploading} onClick={() => fileInputRef.current?.click()}>
                            <ImagePlus className="h-4 w-4" />{' '}
                            {uploading ? 'Mengunggah...' : settings.about_image_url ? 'Ubah Gambar' : 'Tambah Gambar'}
                        </Button>
                        {settings.about_image_url && (
                            <Button type="button" size="sm" variant="outline" onClick={removeImage}>
                                <Trash2 className="h-4 w-4" /> Hapus
                            </Button>
                        )}
                    </div>
                    <input ref={fileInputRef} type="file" accept="image/*" className="hidden" onChange={onPickFile} />
                    <ImageCropperDialog
                        open={cropSrc !== null}
                        imageSrc={cropSrc}
                        aspect={16 / 9}
                        processing={uploading}
                        onClose={() => setCropSrc(null)}
                        onCropped={onCropped}
                    />
                </CardContent>
            </Card>

            <Card className="lg:col-span-2">
                <CardContent className="p-6">
                    <form onSubmit={submit} className="space-y-4">
                        <div className="grid gap-2">
                            <Label htmlFor="about_description">Deskripsi</Label>
                            <textarea
                                id="about_description"
                                rows={14}
                                value={data.about_description}
                                onChange={(e) => setData('about_description', e.target.value)}
                                placeholder="Tekan Enter untuk paragraf baru."
                                className={TEXTAREA_CLASS}
                            />
                            <InputError message={errors.about_description} />
                        </div>
                        <div className="flex items-center gap-3">
                            <Button type="submit" disabled={processing}>
                                Simpan
                            </Button>
                            {recentlySuccessful && <span className="text-muted-foreground text-sm">Tersimpan</span>}
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

function LokasiEditor({ settings }: { settings: Settings }) {
    const { data, setData, put, processing, errors, recentlySuccessful } = useForm({
        address: settings.address ?? '',
        maps_url: settings.maps_url ?? '',
        map_embed_url: settings.map_embed_url ?? '',
    });

    const fileInputRef = useRef<HTMLInputElement>(null);
    const [cropSrc, setCropSrc] = useState<string | null>(null);
    const [uploading, setUploading] = useState(false);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('bajem-benowo.settings.update'), { preserveScroll: true });
    };

    const onPickFile = (e: ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) setCropSrc(URL.createObjectURL(file));
        e.target.value = '';
    };

    const onCropped = (blob: Blob) => {
        setUploading(true);
        router.post(
            route('bajem-benowo.settings.image.update', 'location'),
            { image: new File([blob], 'location.jpg', { type: 'image/jpeg' }) },
            { forceFormData: true, preserveScroll: true, onSuccess: () => setCropSrc(null), onFinish: () => setUploading(false) },
        );
    };

    const removeImage = () => {
        if (confirm('Hapus gambar Lokasi ini?')) {
            router.delete(route('bajem-benowo.settings.image.destroy', 'location'), { preserveScroll: true });
        }
    };

    return (
        <div className="grid gap-6 lg:grid-cols-3">
            <Card className="lg:col-span-1">
                <CardContent className="space-y-4 p-6">
                    <h2 className="font-semibold">Foto Lokasi</h2>
                    <div className="bg-muted aspect-[4/3] overflow-hidden rounded-lg border">
                        {settings.location_image_url ? (
                            <img src={settings.location_image_url} alt="" className="h-full w-full object-cover" />
                        ) : (
                            <div className="text-muted-foreground flex h-full items-center justify-center text-sm">Belum ada gambar</div>
                        )}
                    </div>
                    <div className="flex gap-2">
                        <Button type="button" size="sm" disabled={uploading} onClick={() => fileInputRef.current?.click()}>
                            <ImagePlus className="h-4 w-4" />{' '}
                            {uploading ? 'Mengunggah...' : settings.location_image_url ? 'Ubah Gambar' : 'Tambah Gambar'}
                        </Button>
                        {settings.location_image_url && (
                            <Button type="button" size="sm" variant="outline" onClick={removeImage}>
                                <Trash2 className="h-4 w-4" /> Hapus
                            </Button>
                        )}
                    </div>
                    <input ref={fileInputRef} type="file" accept="image/*" className="hidden" onChange={onPickFile} />
                    <ImageCropperDialog
                        open={cropSrc !== null}
                        imageSrc={cropSrc}
                        aspect={4 / 3}
                        processing={uploading}
                        onClose={() => setCropSrc(null)}
                        onCropped={onCropped}
                    />
                </CardContent>
            </Card>

            <Card className="lg:col-span-2">
                <CardContent className="p-6">
                    <form onSubmit={submit} className="space-y-4">
                        <div className="grid gap-2">
                            <Label htmlFor="address">Alamat</Label>
                            <textarea
                                id="address"
                                rows={3}
                                value={data.address}
                                onChange={(e) => setData('address', e.target.value)}
                                placeholder={'Jl. Pd. Benowo Indah Blk. OO1-5,\nSurabaya'}
                                className={TEXTAREA_CLASS}
                            />
                            <InputError message={errors.address} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="maps_url">Link Google Maps</Label>
                            <Input
                                id="maps_url"
                                type="url"
                                value={data.maps_url}
                                onChange={(e) => setData('maps_url', e.target.value)}
                                placeholder="https://www.google.com/maps/search/?api=1&query=..."
                            />
                            <InputError message={errors.maps_url} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="map_embed_url">URL Embed Peta</Label>
                            <textarea
                                id="map_embed_url"
                                rows={3}
                                value={data.map_embed_url}
                                onChange={(e) => setData('map_embed_url', e.target.value)}
                                placeholder="https://www.google.com/maps/embed?pb=..."
                                className={TEXTAREA_CLASS}
                            />
                            <InputError message={errors.map_embed_url} />
                            <p className="text-muted-foreground text-xs">
                                Ambil dari Google Maps &rarr; Bagikan &rarr; Sematkan peta &rarr; salin URL di atribut src iframe.
                            </p>
                        </div>

                        <div className="flex items-center gap-3">
                            <Button type="submit" disabled={processing}>
                                Simpan
                            </Button>
                            {recentlySuccessful && <span className="text-muted-foreground text-sm">Tersimpan</span>}
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

function ItemsManager({ section, items, intro }: { section: 'ibadah' | 'pelayanan'; items: Item[]; intro?: string | null }) {
    const [selectedId, setSelectedId] = useState<number | null>(null);
    const [adding, setAdding] = useState(false);

    const selected = adding ? null : (items.find((i) => i.id === selectedId) ?? null);
    const label = section === 'ibadah' ? 'Ibadah' : 'Pelayanan';

    const select = (id: number) => {
        setAdding(false);
        setSelectedId(id);
    };

    const startAdd = () => {
        setAdding(true);
        setSelectedId(null);
    };

    const reorder = (ids: number[]) => {
        router.put(route('bajem-benowo.items.reorder'), { section, ids }, { preserveScroll: true });
    };

    return (
        <div className="space-y-6">
            {section === 'pelayanan' && <PelayananIntroEditor intro={intro ?? ''} />}

            <div className="grid gap-6 lg:grid-cols-[22rem_1fr]">
                <Card>
                    <CardContent className="space-y-4 p-4">
                        <Button className="w-full" onClick={startAdd}>
                            <ListPlus className="h-4 w-4" /> Tambah {label}
                        </Button>

                        {items.length > 0 ? (
                            <SortableItemList
                                items={items.map((i) => ({ id: i.id, title: i.title, image_url: i.image_url }))}
                                selectedId={selectedId}
                                onSelect={select}
                                onReorder={reorder}
                            />
                        ) : (
                            <p className="text-muted-foreground py-6 text-center text-sm">Belum ada data.</p>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-6">
                        {adding ? (
                            <ItemForm section={section} onDone={() => setAdding(false)} />
                        ) : selected ? (
                            <ItemForm key={selected.id} section={section} item={selected} onDeleted={() => setSelectedId(null)} />
                        ) : (
                            <div className="text-muted-foreground flex h-full min-h-48 items-center justify-center text-center text-sm">
                                Pilih item di sebelah kiri untuk mengubah, atau klik &ldquo;Tambah {label}&rdquo;.
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}

function PelayananIntroEditor({ intro }: { intro: string }) {
    const { data, setData, put, processing, recentlySuccessful } = useForm({ pelayanan_intro: intro });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('bajem-benowo.settings.update'), { preserveScroll: true });
    };

    return (
        <Card>
            <CardContent className="p-6">
                <form onSubmit={submit} className="space-y-3">
                    <Label htmlFor="pelayanan_intro">Kalimat Pengantar</Label>
                    <textarea
                        id="pelayanan_intro"
                        rows={2}
                        value={data.pelayanan_intro}
                        onChange={(e) => setData('pelayanan_intro', e.target.value)}
                        placeholder="Di luar ibadah mingguan, jemaat Bajem Benowo bertumbuh melalui pelayanan-pelayanan berikut."
                        className={TEXTAREA_CLASS}
                    />
                    <div className="flex items-center gap-3">
                        <Button type="submit" size="sm" disabled={processing}>
                            Simpan
                        </Button>
                        {recentlySuccessful && <span className="text-muted-foreground text-sm">Tersimpan</span>}
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}

function ItemForm({
    section,
    item,
    onDone,
    onDeleted,
}: {
    section: 'ibadah' | 'pelayanan';
    item?: Item;
    onDone?: () => void;
    onDeleted?: () => void;
}) {
    const isNew = !item;
    const { data, setData, post, put, processing, errors, recentlySuccessful, reset } = useForm({
        section,
        title: item?.title ?? '',
        description: item?.description ?? '',
        schedules: item?.schedules ?? [],
        location: item?.location ?? '',
        audience: item?.audience ?? '',
        cadence: item?.cadence ?? '',
    });

    const fileInputRef = useRef<HTMLInputElement>(null);
    const [cropSrc, setCropSrc] = useState<string | null>(null);
    const [uploading, setUploading] = useState(false);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (isNew) {
            post(route('bajem-benowo.items.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    reset();
                    onDone?.();
                },
            });
        } else {
            put(route('bajem-benowo.items.update', item.id), { preserveScroll: true });
        }
    };

    const onPickFile = (e: ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) setCropSrc(URL.createObjectURL(file));
        e.target.value = '';
    };

    const onCropped = (blob: Blob) => {
        if (!item) return;
        setUploading(true);
        router.post(
            route('bajem-benowo.items.image.update', item.id),
            { image: new File([blob], 'item.jpg', { type: 'image/jpeg' }) },
            { forceFormData: true, preserveScroll: true, onSuccess: () => setCropSrc(null), onFinish: () => setUploading(false) },
        );
    };

    const removeImage = () => {
        if (item && confirm('Hapus gambar ini?')) {
            router.delete(route('bajem-benowo.items.image.destroy', item.id), { preserveScroll: true });
        }
    };

    const remove = () => {
        if (item && confirm(`Hapus "${item.title}"?`)) {
            router.delete(route('bajem-benowo.items.destroy', item.id), { preserveScroll: true, onSuccess: onDeleted });
        }
    };

    return (
        <form onSubmit={submit} className="space-y-4">
            <div className="flex items-center justify-between">
                <h2 className="font-semibold">{isNew ? 'Tambah Item' : 'Ubah Item'}</h2>
                {!isNew && (
                    <Button type="button" variant="outline" size="sm" onClick={remove}>
                        <Trash2 className="text-destructive h-4 w-4" /> Hapus
                    </Button>
                )}
            </div>

            {!isNew && (
                <div className="flex items-end gap-4">
                    <div className="bg-muted aspect-video w-40 shrink-0 overflow-hidden rounded-lg border">
                        {item?.image_url ? (
                            <img src={item.image_url} alt="" className="h-full w-full object-cover" />
                        ) : (
                            <div className="text-muted-foreground flex h-full items-center justify-center text-xs">Belum ada gambar</div>
                        )}
                    </div>
                    <div className="flex gap-2">
                        <Button type="button" variant="outline" size="sm" disabled={uploading} onClick={() => fileInputRef.current?.click()}>
                            <ImagePlus className="h-4 w-4" /> {uploading ? 'Mengunggah...' : item?.image_url ? 'Ubah Gambar' : 'Pilih Gambar'}
                        </Button>
                        {item?.image_url && (
                            <Button type="button" variant="outline" size="sm" onClick={removeImage}>
                                <Trash2 className="h-4 w-4" />
                            </Button>
                        )}
                    </div>
                    <input ref={fileInputRef} type="file" accept="image/*" className="hidden" onChange={onPickFile} />
                    <ImageCropperDialog
                        open={cropSrc !== null}
                        imageSrc={cropSrc}
                        aspect={16 / 9}
                        processing={uploading}
                        onClose={() => setCropSrc(null)}
                        onCropped={onCropped}
                    />
                </div>
            )}
            {isNew && <p className="text-muted-foreground text-xs">Gambar dapat ditambahkan setelah item disimpan.</p>}

            <div className="grid gap-2">
                <Label htmlFor="title">Judul</Label>
                <Input id="title" value={data.title} onChange={(e) => setData('title', e.target.value)} required />
                <InputError message={errors.title} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="description">Deskripsi</Label>
                <textarea
                    id="description"
                    rows={4}
                    value={data.description}
                    onChange={(e) => setData('description', e.target.value)}
                    className={TEXTAREA_CLASS}
                />
                <InputError message={errors.description} />
            </div>

            <div className="grid gap-2">
                <Label>Jadwal</Label>
                {data.schedules.map((schedule, index) => (
                    <div key={index} className="flex gap-2">
                        <Input
                            value={schedule}
                            placeholder="Minggu · 09.00 WIB"
                            onChange={(e) => {
                                const next = [...data.schedules];
                                next[index] = e.target.value;
                                setData('schedules', next);
                            }}
                        />
                        <Button
                            type="button"
                            variant="outline"
                            size="icon"
                            aria-label="Hapus jadwal"
                            onClick={() =>
                                setData(
                                    'schedules',
                                    data.schedules.filter((_, i) => i !== index),
                                )
                            }
                        >
                            <Trash2 className="h-4 w-4" />
                        </Button>
                    </div>
                ))}
                <Button type="button" variant="outline" size="sm" className="w-fit" onClick={() => setData('schedules', [...data.schedules, ''])}>
                    <Plus className="h-4 w-4" /> Tambah Jadwal
                </Button>
            </div>

            <div className="grid gap-2">
                <Label htmlFor="location">Lokasi</Label>
                <Input id="location" value={data.location} onChange={(e) => setData('location', e.target.value)} />
                <InputError message={errors.location} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="audience">Sasaran</Label>
                <Input id="audience" value={data.audience} onChange={(e) => setData('audience', e.target.value)} />
                <InputError message={errors.audience} />
            </div>

            {section === 'pelayanan' && (
                <div className="grid gap-2">
                    <Label htmlFor="cadence">Frekuensi</Label>
                    <Input
                        id="cadence"
                        value={data.cadence}
                        onChange={(e) => setData('cadence', e.target.value)}
                        placeholder="mis. Mingguan, 2× sebulan, Sesuai Perjanjian"
                    />
                    <InputError message={errors.cadence} />
                </div>
            )}

            <div className="flex items-center gap-3">
                <Button type="submit" disabled={processing || !data.title}>
                    Simpan
                </Button>
                {isNew && onDone && (
                    <Button type="button" variant="outline" onClick={onDone}>
                        Batal
                    </Button>
                )}
                {recentlySuccessful && <span className="text-muted-foreground text-sm">Tersimpan</span>}
            </div>
        </form>
    );
}

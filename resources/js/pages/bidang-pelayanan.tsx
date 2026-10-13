import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { DndContext, type DragEndEvent, PointerSensor, closestCenter, useSensor, useSensors } from '@dnd-kit/core';
import { SortableContext, arrayMove, useSortable, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { Head, router, useForm } from '@inertiajs/react';
import { GripVertical, Pencil, Plus, Trash2 } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';

interface BidangPelayanan {
    id: number;
    name: string;
    pic_name: string;
    pic_phone: string;
    order: number;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Bidang Pelayanan',
        href: '/bidang-pelayanan',
    },
];

export default function BidangPelayananPage({ bidangPelayanan }: { bidangPelayanan: BidangPelayanan[] }) {
    // null = dialog closed, 'new' = adding, otherwise the row being edited.
    const [editing, setEditing] = useState<BidangPelayanan | 'new' | null>(null);

    const sensors = useSensors(useSensor(PointerSensor, { activationConstraint: { distance: 5 } }));
    const serverSignature = bidangPelayanan.map((b) => b.id).join(',');
    const [order, setOrder] = useState(bidangPelayanan);

    // Resync when the server data changes; keep unsaved local drags otherwise.
    useEffect(() => {
        setOrder(bidangPelayanan);
    }, [bidangPelayanan]);

    const dirty = order.map((b) => b.id).join(',') !== serverSignature;

    const handleDragEnd = (event: DragEndEvent) => {
        const { active, over } = event;
        if (over && active.id !== over.id) {
            const oldIndex = order.findIndex((b) => b.id === active.id);
            const newIndex = order.findIndex((b) => b.id === over.id);
            setOrder(arrayMove(order, oldIndex, newIndex));
        }
    };

    const saveOrder = () => {
        router.put(route('bidang-pelayanan.reorder'), { ids: order.map((b) => b.id) }, { preserveScroll: true });
    };

    const remove = (item: BidangPelayanan) => {
        if (confirm(`Hapus ${item.name}?`)) {
            router.delete(route('bidang-pelayanan.destroy', item.id), { preserveScroll: true });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Bidang Pelayanan" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Bidang Pelayanan</h1>
                        <p className="text-muted-foreground text-sm">Kelola daftar bidang pelayanan beserta PIC dan nomor teleponnya.</p>
                    </div>
                    <Button onClick={() => setEditing('new')}>
                        <Plus className="h-4 w-4" /> Tambah Bidang Pelayanan
                    </Button>
                </div>

                <Card>
                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead className="text-muted-foreground border-b text-left">
                                    <tr>
                                        <th className="w-10 px-2 py-3" />
                                        <th className="px-4 py-3 font-medium">Nama Bidang</th>
                                        <th className="px-4 py-3 font-medium">Nama PIC</th>
                                        <th className="px-4 py-3 font-medium">No. Telepon</th>
                                        <th className="px-4 py-3 text-right font-medium">Aksi</th>
                                    </tr>
                                </thead>
                                <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
                                    <SortableContext items={order.map((b) => b.id)} strategy={verticalListSortingStrategy}>
                                        <tbody>
                                            {order.map((item) => (
                                                <SortableRow
                                                    key={item.id}
                                                    item={item}
                                                    onEdit={() => setEditing(item)}
                                                    onDelete={() => remove(item)}
                                                />
                                            ))}
                                            {order.length === 0 && (
                                                <tr>
                                                    <td colSpan={5} className="text-muted-foreground px-4 py-10 text-center">
                                                        Belum ada data.
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </SortableContext>
                                </DndContext>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                {dirty && (
                    <div className="flex items-center gap-3">
                        <Button type="button" size="sm" onClick={saveOrder}>
                            Simpan Urutan
                        </Button>
                        <span className="text-muted-foreground text-sm">Belum disimpan</span>
                    </div>
                )}
            </div>

            <Dialog open={editing !== null} onOpenChange={(open) => !open && setEditing(null)}>
                <DialogContent>
                    {editing !== null && (
                        <BidangForm
                            key={editing === 'new' ? 'new' : editing.id}
                            item={editing === 'new' ? null : editing}
                            onDone={() => setEditing(null)}
                        />
                    )}
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}

function SortableRow({ item, onEdit, onDelete }: { item: BidangPelayanan; onEdit: () => void; onDelete: () => void }) {
    const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({ id: item.id });
    const style = { transform: CSS.Transform.toString(transform), transition, opacity: isDragging ? 0.5 : 1 };

    return (
        <tr ref={setNodeRef} style={style} className="bg-card border-b last:border-0">
            <td className="px-2 py-3">
                <button
                    {...attributes}
                    {...listeners}
                    type="button"
                    className="text-muted-foreground cursor-grab touch-none active:cursor-grabbing"
                    aria-label="Geser untuk mengatur urutan"
                >
                    <GripVertical className="h-5 w-5" />
                </button>
            </td>
            <td className="px-4 py-3 font-medium">{item.name}</td>
            <td className="px-4 py-3">{item.pic_name}</td>
            <td className="text-muted-foreground px-4 py-3 whitespace-nowrap">{item.pic_phone}</td>
            <td className="px-4 py-3">
                <div className="flex justify-end gap-1">
                    <Button type="button" variant="ghost" size="icon" onClick={onEdit} aria-label="Ubah">
                        <Pencil className="h-4 w-4" />
                    </Button>
                    <Button type="button" variant="ghost" size="icon" onClick={onDelete} aria-label="Hapus">
                        <Trash2 className="text-destructive h-4 w-4" />
                    </Button>
                </div>
            </td>
        </tr>
    );
}

function BidangForm({ item, onDone }: { item: BidangPelayanan | null; onDone: () => void }) {
    const { data, setData, post, put, processing, errors } = useForm({
        name: item?.name ?? '',
        pic_name: item?.pic_name ?? '',
        pic_phone: item?.pic_phone ?? '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        const options = { preserveScroll: true, onSuccess: onDone };

        if (item) {
            put(route('bidang-pelayanan.update', item.id), options);
        } else {
            post(route('bidang-pelayanan.store'), options);
        }
    };

    return (
        <form onSubmit={submit} className="space-y-4">
            <DialogHeader>
                <DialogTitle>{item ? 'Ubah Bidang Pelayanan' : 'Tambah Bidang Pelayanan'}</DialogTitle>
                <DialogDescription>Semua kolom wajib diisi.</DialogDescription>
            </DialogHeader>

            <div className="grid gap-2">
                <Label htmlFor="name">Nama Bidang Pelayanan</Label>
                <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} required />
                <InputError message={errors.name} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="pic_name">Nama PIC Pelayanan</Label>
                <Input id="pic_name" value={data.pic_name} onChange={(e) => setData('pic_name', e.target.value)} required />
                <InputError message={errors.pic_name} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="pic_phone">Nomor Telepon PIC</Label>
                <Input
                    id="pic_phone"
                    inputMode="numeric"
                    value={data.pic_phone}
                    onChange={(e) => setData('pic_phone', e.target.value.trim())}
                    placeholder="6281234567890"
                    required
                />
                <p className="text-muted-foreground text-xs">Diawali 628, hanya angka, tanpa spasi atau tanda hubung.</p>
                <InputError message={errors.pic_phone} />
            </div>

            <DialogFooter>
                <Button type="button" variant="outline" onClick={onDone}>
                    Batal
                </Button>
                <Button type="submit" disabled={processing || !data.name || !data.pic_name || !data.pic_phone}>
                    Simpan
                </Button>
            </DialogFooter>
        </form>
    );
}

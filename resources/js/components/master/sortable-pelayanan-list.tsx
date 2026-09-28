import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import { DndContext, type DragEndEvent, PointerSensor, closestCenter, useSensor, useSensors } from '@dnd-kit/core';
import { SortableContext, arrayMove, useSortable, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { GripVertical, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';

export interface MasterPelayananItem {
    id: number;
    slug: string | null;
    title: string;
    images_count: number;
    details_count: number;
}

function SortableRow({
    item,
    onRename,
    onDelete,
    busy,
}: {
    item: MasterPelayananItem;
    onRename: (id: number, name: string) => void;
    onDelete: (id: number) => void;
    busy: boolean;
}) {
    const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({ id: item.id });
    const style = { transform: CSS.Transform.toString(transform), transition, opacity: isDragging ? 0.5 : 1 };

    const [name, setName] = useState(item.title);
    useEffect(() => setName(item.title), [item.title]);

    const dirty = name.trim() !== item.title;

    return (
        <div ref={setNodeRef} style={style} className={cn('bg-card flex items-center gap-2 rounded-lg border p-2')}>
            <button
                {...attributes}
                {...listeners}
                type="button"
                className="text-muted-foreground cursor-grab touch-none active:cursor-grabbing"
                aria-label="Geser untuk mengatur urutan"
            >
                <GripVertical className="h-5 w-5" />
            </button>

            <div className="min-w-0 flex-1 space-y-1">
                <Input value={name} onChange={(e) => setName(e.target.value)} placeholder="Nama pelayanan" />
                <div className="text-muted-foreground flex flex-wrap items-center gap-x-3 gap-y-0.5 px-1 text-xs">
                    <span>Slug: {item.slug ?? '—'}</span>
                    <span>{item.images_count} gambar</span>
                    <span>{item.details_count} kartu detail</span>
                </div>
            </div>

            {dirty && (
                <Button type="button" size="sm" disabled={busy || !name.trim()} onClick={() => onRename(item.id, name.trim())}>
                    Simpan
                </Button>
            )}

            <Button
                type="button"
                variant="ghost"
                size="icon"
                className="shrink-0"
                disabled={busy}
                onClick={() => onDelete(item.id)}
                aria-label="Hapus pelayanan"
            >
                <Trash2 className="text-destructive h-4 w-4" />
            </Button>
        </div>
    );
}

export default function SortablePelayananList({
    items,
    onReorder,
    onRename,
    onDelete,
    busy,
}: {
    items: MasterPelayananItem[];
    onReorder: (ids: number[]) => void;
    onRename: (id: number, name: string) => void;
    onDelete: (id: number) => void;
    busy: boolean;
}) {
    const sensors = useSensors(useSensor(PointerSensor, { activationConstraint: { distance: 5 } }));
    const serverSignature = items.map((i) => i.id).join(',');
    const [order, setOrder] = useState(items);

    useEffect(() => {
        setOrder(items);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [serverSignature]);

    const dirty = order.map((i) => i.id).join(',') !== serverSignature;

    const handleDragEnd = (event: DragEndEvent) => {
        const { active, over } = event;
        if (over && active.id !== over.id) {
            const oldIndex = order.findIndex((i) => i.id === active.id);
            const newIndex = order.findIndex((i) => i.id === over.id);
            setOrder(arrayMove(order, oldIndex, newIndex));
        }
    };

    return (
        <div className="space-y-3">
            <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
                <SortableContext items={order.map((i) => i.id)} strategy={verticalListSortingStrategy}>
                    <div className="space-y-2">
                        {order.map((item) => (
                            <SortableRow key={item.id} item={item} onRename={onRename} onDelete={onDelete} busy={busy} />
                        ))}
                    </div>
                </SortableContext>
            </DndContext>

            {dirty && (
                <div className="flex items-center gap-3">
                    <Button type="button" size="sm" disabled={busy} onClick={() => onReorder(order.map((i) => i.id))}>
                        Simpan Urutan
                    </Button>
                    <span className="text-muted-foreground text-sm">Urutan belum disimpan</span>
                </div>
            )}
        </div>
    );
}

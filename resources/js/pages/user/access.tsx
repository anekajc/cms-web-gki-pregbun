import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

interface AccessNode {
    key: string;
    label: string;
    /** A page with no sections: its own key is what gets stored. */
    leaf: boolean;
    children: { key: string; label: string }[];
}

interface Props {
    user: { id: number; name: string; email: string };
    tree: AccessNode[];
    granted: string[];
}

export default function UserAccess({ user, tree, granted }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'User', href: '/user' },
        { title: 'Set Pemakai', href: route('user.access.edit', user.id) },
    ];

    const { data, setData, put, processing, errors } = useForm<{ permissions: string[] }>({ permissions: granted });

    // A parent checkbox only reveals its sections; access itself comes from the
    // checked children. Parents start open when any of their children is granted.
    const [open, setOpen] = useState<Set<string>>(
        () => new Set(tree.filter((node) => node.children.some((child) => granted.includes(child.key))).map((node) => node.key)),
    );

    const has = (key: string) => data.permissions.includes(key);

    const toggleKey = (key: string, checked: boolean) =>
        setData('permissions', checked ? [...data.permissions, key] : data.permissions.filter((k) => k !== key));

    const toggleParent = (node: AccessNode, checked: boolean) => {
        setOpen((prev) => {
            const next = new Set(prev);
            if (checked) {
                next.add(node.key);
            } else {
                next.delete(node.key);
            }
            return next;
        });

        // Closing a parent revokes all of its sections.
        if (!checked) {
            const childKeys = node.children.map((child) => child.key);
            setData(
                'permissions',
                data.permissions.filter((k) => !childKeys.includes(k)),
            );
        }
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('user.access.update', user.id));
    };

    const permissionError = errors.permissions ?? Object.entries(errors).find(([field]) => field.startsWith('permissions.'))?.[1];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Set Pemakai" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">Set Pemakai</h1>
                    <p className="text-muted-foreground text-sm">
                        Atur menu yang dapat diakses oleh <span className="text-foreground font-medium">{user.name}</span> ({user.email}). Centang
                        menu untuk menampilkan bagiannya, lalu centang bagian yang boleh dikelola.
                    </p>
                </div>

                <Card className="max-w-xl">
                    <CardContent className="p-6">
                        <form onSubmit={submit} className="space-y-6">
                            <ul className="space-y-4">
                                {tree.map((node) => {
                                    const parentId = `access-${node.key}`;

                                    if (node.leaf) {
                                        return (
                                            <li key={node.key} className="flex items-center gap-3">
                                                <Checkbox
                                                    id={parentId}
                                                    checked={has(node.key)}
                                                    onCheckedChange={(c) => toggleKey(node.key, c === true)}
                                                />
                                                <Label htmlFor={parentId} className="font-medium">
                                                    {node.label}
                                                </Label>
                                            </li>
                                        );
                                    }

                                    const isOpen = open.has(node.key);
                                    const noneChecked = !node.children.some((child) => has(child.key));

                                    return (
                                        <li key={node.key} className="space-y-2">
                                            <div className="flex items-center gap-3">
                                                <Checkbox id={parentId} checked={isOpen} onCheckedChange={(c) => toggleParent(node, c === true)} />
                                                <Label htmlFor={parentId} className="font-medium">
                                                    {node.label}
                                                </Label>
                                            </div>

                                            {isOpen && (
                                                <div className="ml-8 space-y-2 border-l pl-4">
                                                    {node.children.length === 0 && (
                                                        <p className="text-muted-foreground text-xs">Belum ada bagian untuk menu ini.</p>
                                                    )}
                                                    {node.children.map((child) => {
                                                        const childId = `access-${child.key}`;
                                                        return (
                                                            <div key={child.key} className="flex items-center gap-3">
                                                                <Checkbox
                                                                    id={childId}
                                                                    checked={has(child.key)}
                                                                    onCheckedChange={(c) => toggleKey(child.key, c === true)}
                                                                />
                                                                <Label htmlFor={childId} className="font-normal">
                                                                    {child.label}
                                                                </Label>
                                                            </div>
                                                        );
                                                    })}
                                                    {node.children.length > 0 && noneChecked && (
                                                        <p className="text-muted-foreground text-xs">Pilih minimal satu sub-menu.</p>
                                                    )}
                                                </div>
                                            )}
                                        </li>
                                    );
                                })}
                            </ul>

                            <InputError message={permissionError} />

                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Simpan
                                </Button>
                                <Button type="button" variant="outline" asChild>
                                    <Link href={route('user')}>Batal</Link>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

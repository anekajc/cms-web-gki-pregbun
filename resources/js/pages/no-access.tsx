import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { ShieldOff } from 'lucide-react';

export default function NoAccess() {
    return (
        <AppLayout>
            <Head title="Belum Ada Akses" />

            <div className="flex h-full flex-1 items-center justify-center p-4">
                <Card className="max-w-md">
                    <CardContent className="flex flex-col items-center gap-3 p-8 text-center">
                        <ShieldOff className="text-muted-foreground h-10 w-10" />
                        <h1 className="text-lg font-semibold">Belum ada akses</h1>
                        <p className="text-muted-foreground text-sm">
                            Anda belum memiliki akses ke menu mana pun. Hubungi admin untuk mengatur menu yang dapat Anda kelola.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

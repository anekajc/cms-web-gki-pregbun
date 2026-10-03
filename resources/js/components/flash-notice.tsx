import { router } from '@inertiajs/react';
import { CheckCircle2, X } from 'lucide-react';
import { useEffect, useState } from 'react';

const DISMISS_MS = 4000;

/**
 * Auto-dismissing banner for the `success` flash message the controllers set
 * after every mutation. Listens to Inertia's `success` event (not a prop
 * effect) so two identical messages in a row still show up.
 */
export default function FlashNotice() {
    const [message, setMessage] = useState<string | null>(null);

    useEffect(() => {
        return router.on('success', (event) => {
            const flash = (event.detail.page.props as { flash?: { success?: string | null } }).flash;
            if (flash?.success) setMessage(flash.success);
        });
    }, []);

    useEffect(() => {
        if (!message) return;
        const timer = setTimeout(() => setMessage(null), DISMISS_MS);
        return () => clearTimeout(timer);
    }, [message]);

    if (!message) return null;

    return (
        <div
            role="status"
            className="bg-background fixed top-4 right-4 z-[100] flex max-w-sm items-center gap-3 rounded-lg border border-green-600/40 px-4 py-3 text-sm shadow-lg"
        >
            <CheckCircle2 className="h-5 w-5 flex-shrink-0 text-green-600" />
            <span className="flex-1">{message}</span>
            <button type="button" onClick={() => setMessage(null)} className="opacity-60 hover:opacity-100" aria-label="Tutup">
                <X className="h-4 w-4" />
            </button>
        </div>
    );
}

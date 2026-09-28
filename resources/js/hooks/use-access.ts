import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { useCallback } from 'react';

/**
 * Per-user access checks, mirroring User::canAccess / canAccessAny on the server.
 * Admins pass every check, even for pages with no sections yet.
 */
export function useAccess() {
    const { user, access: keys } = usePage<SharedData>().props.auth;
    const isAdmin = user.role === 'admin';

    const can = useCallback((key: string) => isAdmin || keys.includes(key), [isAdmin, keys]);

    // "dashboard" matches "dashboard.warta"; "pelayanan.1" does not match "pelayanan.12".
    const canAny = useCallback((prefix: string) => isAdmin || keys.some((key) => key === prefix || key.startsWith(`${prefix}.`)), [isAdmin, keys]);

    return { can, canAny };
}

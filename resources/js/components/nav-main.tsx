import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';

// [fresh, stale] prefetch cache window. @inertiajs/core accepts an array here
// (PrefetchOptions.cacheFor), but @inertiajs/react 2.0.3's Link props only type a
// single value, so the cast just bridges that typing gap — behavior is unchanged.
const CACHE_FOR = ['30s', '5m'] as unknown as string;

export function NavMain({ items = [], label = 'Platform' }: { items: NavItem[]; label?: string }) {
    const page = usePage();
    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel>{label}</SidebarGroupLabel>
            <SidebarMenu>
                {items.map((item) => (
                    <SidebarMenuItem key={item.title}>
                        <SidebarMenuButton asChild isActive={item.url === page.url}>
                            {/* prefetch on hover + stale-while-revalidate: returning to a page
                                renders instantly from cache (fresh for 30s), and for up to 5m it
                                serves cache while re-fetching in the background so content admins
                                edit elsewhere doesn't stay stale. */}
                            <Link href={item.url} prefetch="hover" cacheFor={CACHE_FOR}>
                                {item.icon && <item.icon />}
                                <span>{item.title}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}

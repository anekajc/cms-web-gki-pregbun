import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { useAccess } from '@/hooks/use-access';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    Building2,
    CalendarArrowUp,
    Church,
    Database,
    Hammer,
    HandCoins,
    HandHeart,
    LayoutDashboard,
    List,
    UserRoundPen,
    UsersRound,
} from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        url: '/dashboard',
        access: 'dashboard',
        icon: LayoutDashboard,
    },
    {
        title: 'User',
        url: '/user',
        icon: UserRoundPen,
    },
    {
        title: 'Tentang Kami',
        url: '/tentangkami',
        access: 'tentangkami',
        icon: UsersRound,
    },
    {
        title: 'Ibadah',
        url: '/kebaktian',
        access: 'ibadah',
        icon: Church,
    },
    {
        title: 'Event',
        url: '/event',
        access: 'event',
        icon: CalendarArrowUp,
    },
    {
        title: 'Pelayanan',
        url: '/pelayanan',
        access: 'pelayanan',
        icon: HandHeart,
    },
    {
        title: 'Bajem Benowo',
        url: '/bajem-benowo',
        access: 'bajem',
        icon: Building2,
    },
    {
        title: 'Komisi',
        url: '/komisi',
        access: 'komisi',
        icon: List,
    },
    {
        title: 'Pembangunan',
        url: '/pembangunan',
        access: 'pembangunan',
        icon: Hammer,
    },
    {
        title: 'Persembahan',
        url: '/persembahan',
        access: 'persembahan',
        icon: HandCoins,
    },
    // {
    //     title: 'Dummy',
    //     url: '/dummy',
    //     icon: Ban,
    // },
];

// Master data: source lists that feature pages' tabs/options are built from.
const masterNavItems: NavItem[] = [
    {
        title: 'Pelayanan',
        url: '/master/pelayanan',
        access: 'master.pelayanan',
        icon: Database,
    },
];

// const footerNavItems: NavItem[] = [
//     {
//         title: 'Repository',
//         url: 'https://github.com/laravel/react-starter-kit',
//         icon: Folder,
//     },
//     {
//         title: 'Documentation',
//         url: 'https://laravel.com/docs/starter-kits',
//         icon: BookOpen,
//     },
// ];

export function AppSidebar() {
    const { auth } = usePage<SharedData>().props;
    const isAdmin = auth.user.role === 'admin';
    const { canAny } = useAccess();

    // User management is admin-only; every other item shows when the user holds
    // at least one section of that page (see App\Support\Access).
    const visible = (item: NavItem) => (item.url === '/user' ? isAdmin : !item.access || canAny(item.access));
    const navItems = mainNavItems.filter(visible);
    const masterItems = masterNavItems.filter(visible);

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={navItems} />
                {masterItems.length > 0 && <NavMain items={masterItems} label="Master" />}
            </SidebarContent>

            <SidebarFooter>
                {/* <NavFooter items={footerNavItems} className="mt-auto" /> */}
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}

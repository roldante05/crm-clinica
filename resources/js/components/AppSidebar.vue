<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BookOpen, FolderGit2, LayoutGrid, Calendar, Stethoscope, Users, UserCog } from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import clinic from '@/routes/clinic';
import { useAuth } from '@/composables/useAuth';
import type { NavItem } from '@/types';

const { hasRole } = useAuth();

const allNavItems: (NavItem & { roles?: string[] })[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Especialidades',
        href: clinic.specialties.index().url,
        icon: Stethoscope,
        roles: ['admin'],
    },
    {
        title: 'Doctores',
        href: clinic.doctors.index().url,
        icon: UserCog,
        roles: ['admin', 'secretary'],
    },
    {
        title: 'Pacientes',
        href: clinic.patients.index().url,
        icon: Users,
        roles: ['admin', 'secretary'],
    },
    {
        title: 'Turnos',
        href: clinic.appointments.index().url,
        icon: Calendar,
    },
];

const mainNavItems = allNavItems.filter((item) => {
    if (!item.roles) {
        return true;
    }
    return hasRole(...item.roles);
});

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

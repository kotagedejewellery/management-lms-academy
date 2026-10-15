<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    Award,
    Bell,
    BookOpen,
    GraduationCap,
    LayoutGrid,
    ListTree,
    Network,
    ShieldCheck,
    Users,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const page = usePage();

const mainNavGroups = computed(() => {
    const learning: NavItem[] = [
        ...(page.props.auth.can.manageCourses
            ? [
                  {
                      title: 'Kelola course',
                      href: '/courses',
                      icon: GraduationCap,
                  },
              ]
            : []),
        ...(page.props.auth.can.manageLearningPaths
            ? [
                  {
                      title: 'Kelola jalur belajar',
                      href: '/learning-paths',
                      icon: ListTree,
                  },
              ]
            : []),
        ...(!page.props.auth.can.manageEnrollments
            ? [
                  {
                      title: 'Pembelajaran saya',
                      href: '/my-learning',
                      icon: GraduationCap,
                  },
                  {
                      title: 'Jalur belajar saya',
                      href: '/my-learning-paths',
                      icon: ListTree,
                  },
                  {
                      title: 'Katalog course',
                      href: '/course-library',
                      icon: BookOpen,
                  },
              ]
            : []),
        { title: 'Sertifikat', href: '/certificates', icon: Award },
    ];
    const management: NavItem[] = [
        ...(page.props.auth.can.manageUsers
            ? [{ title: 'Pengguna', href: '/users', icon: Users }]
            : []),
        ...(page.props.auth.can.manageOrganization
            ? [
                  {
                      title: 'Organisasi',
                      href: '/organization/divisions',
                      icon: Network,
                  },
              ]
            : []),
        ...(page.props.auth.can.manageEnrollments
            ? [
                  { title: 'Penugasan', href: '/enrollments', icon: Users },
                  {
                      title: 'Penugasan jalur',
                      href: '/learning-path-assignments',
                      icon: ListTree,
                  },
              ]
            : []),
    ];
    const monitoring: NavItem[] = [
        ...(!page.props.auth.can.manageEnrollments
            ? [
                  {
                      title: 'Progres saya',
                      href: '/my-progress',
                      icon: BookOpen,
                  },
              ]
            : []),
        ...(page.props.auth.can.manageEnrollments
            ? [
                  {
                      title: 'Pengumuman',
                      href: '/announcements',
                      icon: Bell,
                  },
              ]
            : []),
        { title: 'Notifikasi', href: '/notifications', icon: Bell },
        ...(page.props.auth.can.manageEnrollments ||
        page.props.auth.can.viewReports
            ? [{ title: 'Laporan', href: '/reports', icon: BookOpen }]
            : []),
        ...(page.props.auth.can.viewTeam
            ? [{ title: 'Progres tim', href: '/team-progress', icon: Users }]
            : []),
        ...(page.props.auth.isSuperAdmin
            ? [
                  {
                      title: 'Aktivitas audit',
                      href: '/audit-logs',
                      icon: ShieldCheck,
                  },
              ]
            : []),
    ];

    return [
        {
            title: 'Utama',
            items: [
                { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
            ],
        },
        ...(learning.length
            ? [{ title: 'Pembelajaran', items: learning }]
            : []),
        ...(management.length ? [{ title: 'Kelola', items: management }] : []),
        { title: 'Pantau', items: monitoring },
    ];
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader
            class="border-b border-sidebar-border px-3 py-4 group-data-[collapsible=icon]:p-2"
        >
            <Link
                :href="dashboard()"
                class="flex h-14 items-center justify-center rounded-lg px-2 transition-colors group-data-[collapsible=icon]:size-8 group-data-[collapsible=icon]:p-0 hover:bg-sidebar-accent focus-visible:ring-2 focus-visible:ring-sidebar-ring focus-visible:outline-none"
            >
                <AppLogo class="group-data-[collapsible=icon]:hidden" />
                <AppLogoIcon
                    class="hidden size-8 rounded-lg object-contain group-data-[collapsible=icon]:block"
                />
            </Link>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :groups="mainNavGroups" />
        </SidebarContent>

        <SidebarFooter class="border-t border-sidebar-border p-2">
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

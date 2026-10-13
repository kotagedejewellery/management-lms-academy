<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
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

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    ...(page.props.auth.can.manageUsers
        ? [{ title: 'Users', href: '/users', icon: Users }]
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
    ...(page.props.auth.can.manageCourses
        ? [{ title: 'Courses', href: '/courses', icon: GraduationCap }]
        : []),
    ...(page.props.auth.can.manageLearningPaths
        ? [{ title: 'Learning paths', href: '/learning-paths', icon: ListTree }]
        : []),
    ...(page.props.auth.can.manageEnrollments
        ? [
              { title: 'Assignments', href: '/enrollments', icon: Users },
              {
                  title: 'Path assignments',
                  href: '/learning-path-assignments',
                  icon: ListTree,
              },
              { title: 'Announcements', href: '/announcements', icon: Bell },
          ]
        : [
              {
                  title: 'My Learning',
                  href: '/my-learning',
                  icon: GraduationCap,
              },
              {
                  title: 'Learning Paths',
                  href: '/my-learning-paths',
                  icon: ListTree,
              },
              {
                  title: 'Course Library',
                  href: '/course-library',
                  icon: BookOpen,
              },
              { title: 'My Progress', href: '/my-progress', icon: BookOpen },
          ]),
    { title: 'Notifications', href: '/notifications', icon: Bell },
    ...(page.props.auth.can.manageEnrollments || page.props.auth.can.viewReports
        ? [{ title: 'Reports', href: '/reports', icon: BookOpen }]
        : []),
    ...(page.props.auth.can.viewTeam
        ? [{ title: 'Progres Tim', href: '/team-progress', icon: Users }]
        : []),
    ...(page.props.auth.isSuperAdmin
        ? [{ title: 'Audit activity', href: '/audit-logs', icon: ShieldCheck }]
        : []),
]);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader class="p-3 group-data-[collapsible=icon]:p-2">
            <Link
                :href="dashboard()"
                class="flex h-20 items-center justify-center rounded-lg px-2 transition-colors group-data-[collapsible=icon]:size-8 group-data-[collapsible=icon]:p-0 hover:bg-sidebar-accent focus-visible:ring-2 focus-visible:ring-sidebar-ring focus-visible:outline-none"
            >
                <AppLogo class="group-data-[collapsible=icon]:hidden" />
                <AppLogoIcon
                    class="hidden size-8 rounded-lg object-contain group-data-[collapsible=icon]:block"
                />
            </Link>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
